<?php

namespace App\Livewire\Teacher\Concerns;

use App\Models\Teacher;
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
}
