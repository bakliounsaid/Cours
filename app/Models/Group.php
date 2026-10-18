<?php

namespace App\Models;

use Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    /** @use HasFactory<GroupFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'subject_id',
        'level_id',
        'teacher_id',
        'room_id',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function timeSlots(): BelongsToMany
    {
        return $this->belongsToMany(TimeSlot::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)->withPivot('joined_at');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Places maximum : capacité de la salle, plafonnée à 15 élèves.
     */
    public function capacity(): int
    {
        return min($this->room->capacity, (int) config('educoeur.groups.max_students'));
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
