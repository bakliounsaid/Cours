<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\LessonEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrée du journal d'une séance (jamais modifiée ni supprimée).
 *
 * data selon le type :
 * - checked_in : {present: int, absent: int, late: int}
 * - attendance_updated : {changes: [{student: "Yanis Benali", from: "absent", to: "present"}]}
 * - work_recorded : {summary: ?string, homework: ?string, previous_summary: ?string, previous_homework: ?string}
 * - cancelled : {scope: once|permanent, reason: ?string, slot: ?string}
 */
class LessonEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'lesson_id',
        'user_id',
        'type',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'type' => LessonEventType::class,
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Détail lisible de l'événement, dans la langue courante.
     *
     * @return list<string>
     */
    public function details(): array
    {
        $data = $this->data ?? [];

        return match ($this->type) {
            LessonEventType::CheckedIn => [__('teacher.lessons.events.counts', [
                'present' => $data['present'] ?? 0,
                'late' => $data['late'] ?? 0,
                'absent' => $data['absent'] ?? 0,
            ])],
            LessonEventType::AttendanceUpdated => array_map(fn (array $change) => __('teacher.lessons.events.change', [
                'name' => $change['student'],
                'from' => AttendanceStatus::from($change['from'])->label(),
                'to' => AttendanceStatus::from($change['to'])->label(),
            ]), $data['changes'] ?? []),
            LessonEventType::WorkRecorded => array_values(array_filter([
                filled($data['summary'] ?? null) ? __('teacher.lessons.summary').' : '.$data['summary'] : null,
                filled($data['homework'] ?? null) ? __('teacher.lessons.homework').' : '.$data['homework'] : null,
            ])),
            LessonEventType::Cancelled => array_values(array_filter([
                ($data['scope'] ?? 'once') === 'permanent'
                    ? __('teacher.cancel.scope_permanent_done', ['slot' => $data['slot'] ?? ''])
                    : __('teacher.cancel.scope_once_done'),
                filled($data['reason'] ?? null) ? __('teacher.cancel.reason').' : '.$data['reason'] : null,
            ])),
        };
    }
}
