<?php

namespace App\Livewire\Concerns;

use App\Livewire\Forms\ModelForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Création / modification en modale avec validation en temps réel,
 * et suppression protégée (élément encore utilisé → message clair).
 *
 * Le composant déclare : public SomeForm $form; et implémente findModel().
 *
 * @property ModelForm $form
 */
trait WithModalForm
{
    public bool $showForm = false;

    abstract protected function findModel(int $id): Model;

    public function create(): void
    {
        $this->resetValidation();
        $this->form->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->resetValidation();
        $this->form->load($this->findModel($id));
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->form->save();

        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: __('ui.saved'));
    }

    public function delete(int $id): void
    {
        try {
            // Savepoint : un refus de la base n'invalide pas la transaction en cours.
            DB::transaction(fn () => $this->performDelete($this->findModel($id)));
        } catch (QueryException $exception) {
            // 23503 : violation de clé étrangère (élément encore référencé)
            if ($exception->getCode() !== '23503') {
                throw $exception;
            }

            $this->dispatch('toast', type: 'error', message: __('ui.cannot_delete'));

            return;
        }

        $this->dispatch('toast', type: 'success', message: __('ui.deleted'));
    }

    /**
     * Suppression effective (à surcharger si d'autres enregistrements doivent suivre).
     */
    protected function performDelete(Model $model): void
    {
        $model->delete();
    }

    /**
     * Validation en temps réel du champ modifié.
     */
    public function updated(string $property): void
    {
        if (str_starts_with($property, 'form.')) {
            $this->validateOnly($property);
        }
    }
}
