<?php

namespace App\Models;

use App\Enums\Locale;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'level_id',
        'parent_phone',
        'parent_locale',
        'enrolled_at',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'parent_locale' => Locale::class,
            'enrolled_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name.' '.$this->last_name));
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withPivot('joined_at');
    }

    /** Séances individuelles / spéciales auxquelles l'élève est inscrit directement. */
    public function extraLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany('paid_at');
    }

    public function parentMessages(): HasMany
    {
        return $this->hasMany(ParentMessage::class);
    }

    /**
     * Toutes les séances de l'élève : celles de ses groupes et ses séances individuelles.
     *
     * @return Builder<Lesson>
     */
    public function lessons(): Builder
    {
        return Lesson::query()->where(function (Builder $query) {
            $query->whereIn('group_id', $this->groups()->select('groups.id'))
                ->orWhereIn('id', $this->extraLessons()->select('lessons.id'));
        });
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term) {
            $query->whereLike('first_name', "%{$term}%")
                ->orWhereLike('last_name', "%{$term}%")
                ->orWhereRaw("concat(first_name, ' ', last_name) ilike ?", ["%{$term}%"])
                ->orWhereLike('parent_phone', '%'.preg_replace('/\D/', '', $term).'%');
        });
    }
}
