<?php

namespace App\Models;

use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'rate_per_lesson',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_per_lesson' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(TeacherCheckin::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(TeacherAbsence::class);
    }

    /**
     * Historique complet : ses séances, y compris celles où il a été remplacé.
     *
     * @return Builder<Lesson>
     */
    public function lessonHistory(): Builder
    {
        return Lesson::query()->where(fn (Builder $query) => $query
            ->where('teacher_id', $this->id)
            ->orWhere('original_teacher_id', $this->id));
    }

    /** Nom affiché (celui du compte utilisateur). */
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
