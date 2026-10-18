<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pointage de l'enseignant : enregistré à la validation de l'appel.
 */
class TeacherCheckin extends Model
{
    protected $fillable = [
        'lesson_id',
        'teacher_id',
        'checked_at',
        'rate_amount',
        'corrected_by',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'rate_amount' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
