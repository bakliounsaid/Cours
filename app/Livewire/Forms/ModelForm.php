<?php

namespace App\Livewire\Forms;

use Illuminate\Database\Eloquent\Model;
use Livewire\Form;

/**
 * Formulaire lié à un modèle (création ou modification).
 */
abstract class ModelForm extends Form
{
    public ?int $editingId = null;

    abstract public function load(Model $model): void;

    abstract public function save(): Model;

    public function resetForm(): void
    {
        $this->reset();
    }

    public function isEditing(): bool
    {
        return $this->editingId !== null;
    }
}
