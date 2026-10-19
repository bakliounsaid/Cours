<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Support\Format;
use Carbon\CarbonInterface;
use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected $fillable = [
        'group_id',
        'subject_id',
        'time_slot_id',
        'room_id',
        'teacher_id',
        'original_teacher_id',
        'makeup_of_id',
        'date',
        'starts_at',
        'ends_at',
        'type',
        'status',
        'title',
        'cancel_reason',
        'missing_attendance_alerted',
        'created_by',
    ];

    protected $attributes = [
        'status' => 'scheduled',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'type' => LessonType::class,
            'status' => LessonStatus::class,
            'missing_attendance_alerted' => 'boolean',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'original_teacher_id');
    }

    /** Séance d'origine, pour une séance de rattrapage. */
    public function makeupOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'makeup_of_id');
    }

    public function makeups(): HasMany
    {
        return $this->hasMany(self::class, 'makeup_of_id');
    }

    /** Élèves ajoutés directement (séance individuelle, spéciale…). */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function checkin(): HasOne
    {
        return $this->hasOne(TeacherCheckin::class);
    }

    public function teacherAbsences(): BelongsToMany
    {
        return $this->belongsToMany(TeacherAbsence::class)->withPivot('resolution', 'resolved_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Élèves attendus : ceux du groupe et ceux ajoutés directement.
     *
     * @return Collection<int, Student>
     */
    public function roster(): Collection
    {
        $students = $this->students()->get();

        if ($this->group_id) {
            $students = $this->group->students()->active()->get()->merge($students);
        }

        return $students->unique('id')->sortBy(['last_name', 'first_name'])->values();
    }

    /** Libellé court : nom du groupe ou titre ou élève(s). */
    public function displayName(): string
    {
        if ($this->title) {
            return $this->title;
        }

        if ($this->group) {
            return $this->group->name;
        }

        return $this->students->pluck('full_name')->implode(', ');
    }

    /**
     * Nombre d'élèves attendus, à partir des relations déjà chargées
     * (group.students et students) : aucune requête supplémentaire.
     */
    public function expectedStudentsCount(): int
    {
        $students = $this->group ? $this->group->students->merge($this->students) : $this->students;

        return $students->unique('id')->count();
    }

    /** « soon » (à venir), « live » (en cours) ou « done » (terminée). */
    public function liveState(?CarbonInterface $at = null): string
    {
        $at ??= now();

        return match (true) {
            $this->hasEnded($at) => 'done',
            $at->greaterThanOrEqualTo($this->starts_at) => 'live',
            default => 'soon',
        };
    }

    /** Minutes avant le début (0 si la séance a commencé). */
    public function minutesUntilStart(?CarbonInterface $at = null): int
    {
        return max(0, (int) ceil(($at ?? now())->diffInSeconds($this->starts_at, false) / 60));
    }

    /** Temps restant avant le début : « 25 min », « 1 h 05 ». */
    public function startsInLabel(?CarbonInterface $at = null): string
    {
        return Format::minutes($this->minutesUntilStart($at));
    }

    public function isCancelled(): bool
    {
        return $this->status === LessonStatus::Cancelled;
    }

    public function hasEnded(?CarbonInterface $at = null): bool
    {
        return ($at ?? now())->greaterThanOrEqualTo($this->ends_at);
    }

    #[Scope]
    protected function notCancelled(Builder $query): void
    {
        $query->where('status', '!=', LessonStatus::Cancelled);
    }

    #[Scope]
    protected function between(Builder $query, CarbonInterface $from, CarbonInterface $to): void
    {
        $query->where('starts_at', '<', $to)->where('ends_at', '>', $from);
    }

    #[Scope]
    protected function onDate(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('date', $date->toDateString());
    }

    #[Scope]
    protected function chronological(Builder $query): void
    {
        $query->orderBy('starts_at')->orderBy('id');
    }
}
