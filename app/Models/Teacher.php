<?php

namespace App\Models;

use App\Enums\LessonType;
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
        'rate_group',
        'rate_individual',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate_group' => 'integer',
            'rate_individual' => 'integer',
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

    /** Élèves suivis en cours individuels. */
    public function individualStudents(): BelongsToMany
    {
        return $this->belongsToMany(Student::class)->withPivot('created_at');
    }

    /**
     * Ses élèves : ceux de ses groupes et ceux qu'il suit en cours individuels.
     *
     * @return Builder<Student>
     */
    public function students(): Builder
    {
        return Student::query()->where(function (Builder $query) {
            $query->whereHas('groups', fn (Builder $groups) => $groups->where('teacher_id', $this->id))
                ->orWhereHas('individualTeachers', fn (Builder $teachers) => $teachers->whereKey($this->id));
        });
    }

    /**
     * Tarif d'une séance : individuel pour un cours particulier (ou son rattrapage /
     * remplacement hors groupe), tarif de groupe sinon.
     */
    public function rateFor(Lesson $lesson): int
    {
        $individual = $lesson->type === LessonType::Individual
            || ($lesson->group_id === null && $lesson->type !== LessonType::Special && $lesson->type !== LessonType::Group);

        return $individual ? $this->rate_individual : $this->rate_group;
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
