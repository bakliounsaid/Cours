<?php

namespace App\Livewire\Teacher\Concerns;

use App\Models\Teacher;
use App\Support\Format;
use Livewire\Attributes\Computed;

/**
 * Accès à la fiche de l'enseignant connecté : $this->teacher
 */
trait ForCurrentTeacher
{
    #[Computed]
    public function teacher(): Teacher
    {
        return auth()->user()->teacher;
    }

    /** « Cours individuel : 3 000 DA / séance » */
    #[Computed]
    public function individualRateLabel(): string
    {
        return __('teacher.rates.individual_value', ['amount' => Format::money($this->teacher->rate_individual)]);
    }
}
