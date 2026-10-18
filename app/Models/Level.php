<?php

namespace App\Models;

use App\Enums\Cycle;
use App\Models\Concerns\FlushesCatalog;
use App\Models\Concerns\HasTranslations;
use Database\Factories\LevelFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    /** @use HasFactory<LevelFactory> */
    use FlushesCatalog, HasFactory, HasTranslations;

    /** @var list<string> */
    protected array $translatable = ['name'];

    protected $fillable = [
        'cycle',
        'year',
        'name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'cycle' => Cycle::class,
            'year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
