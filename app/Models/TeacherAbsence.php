<?php

namespace App\Models;

use App\Enums\AbsenceScope;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TeacherAbsence extends Model
{
    protected $fillable = [
        'teacher_id',
        'date',
        'scope',
        'reason',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'scope' => AbsenceScope::class,
            'handled_at' => 'datetime',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class)->withPivot('resolution', 'resolved_at');
    }

    #[Scope]
    protected function unhandled(Builder $query): void
    {
        $query->whereNull('handled_at');
    }
}
