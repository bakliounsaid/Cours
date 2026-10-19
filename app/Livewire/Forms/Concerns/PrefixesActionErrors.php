<?php

namespace App\Livewire\Forms\Concerns;

use Illuminate\Validation\ValidationException;

/**
 * Les actions lèvent des erreurs sur « room_id » ; le formulaire Livewire
 * les affiche sous « form.room_id ».
 */
trait PrefixesActionErrors
{
    /**
     * @template TResult
     *
     * @param  callable(): TResult  $callback
     * @return TResult
     */
    protected function withFormErrors(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(collect($exception->errors())
                ->mapWithKeys(fn (array $messages, string $key) => [$this->getPropertyName().'.'.$key => $messages])
                ->all());
        }
    }
}
