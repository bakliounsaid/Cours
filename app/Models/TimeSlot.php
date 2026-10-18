<?php

namespace App\Models;

use App\Models\Concerns\FlushesCatalog;
use App\Support\Format;
use Carbon\CarbonInterface;
use Database\Factories\TimeSlotFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class TimeSlot extends Model
{
    /** @use HasFactory<TimeSlotFactory> */
    use FlushesCatalog, HasFactory;

    protected $fillable = [
        'day_of_week',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    /** « 17:00 » */
    public function startTime(): string
    {
        return substr($this->starts_at, 0, 5);
    }

    /** « 19:00 » */
    public function endTime(): string
    {
        return substr($this->ends_at, 0, 5);
    }

    /** « mardi · 17:00 – 19:00 » */
    public function label(): string
    {
        return Format::weekday($this->day_of_week).' · '.$this->startTime().' – '.$this->endTime();
    }

    /** Début de ce créneau à une date donnée. */
    public function startsOn(CarbonInterface $date): Carbon
    {
        return Carbon::parse($date->toDateString().' '.$this->starts_at);
    }

    /** Fin de ce créneau à une date donnée. */
    public function endsOn(CarbonInterface $date): Carbon
    {
        return Carbon::parse($date->toDateString().' '.$this->ends_at);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('day_of_week')->orderBy('starts_at');
    }

    #[Scope]
    protected function onDay(Builder $query, int $dayOfWeek): void
    {
        $query->where('day_of_week', $dayOfWeek);
    }
}
