<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\Locale;
use Database\Factories\EnrollmentRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentRequest extends Model
{
    /** @use HasFactory<EnrollmentRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'student_name',
        'level_id',
        'subject_ids',
        'parent_phone',
        'parent_locale',
        'message',
        'status',
        'student_id',
        'processed_at',
        'processed_by',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'subject_ids' => 'array',
            'parent_locale' => Locale::class,
            'status' => EnrollmentStatus::class,
            'processed_at' => 'datetime',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return Collection<int, Subject>
     */
    public function subjects(): Collection
    {
        return Subject::query()->whereIn('id', $this->subject_ids ?? [])->ordered()->get();
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', EnrollmentStatus::New);
    }
}
