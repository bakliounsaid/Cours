<?php

namespace App\Livewire\Forms;

use App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class RoomForm extends ModelForm
{
    public string $name = '';

    public int|string $capacity = 15;

    public string $color = '#2F5D50';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60', Rule::unique('rooms', 'name')->ignore($this->editingId)],
            'capacity' => ['required', 'integer', 'min:1', 'max:'.config('educoeur.groups.max_students')],
            'color' => ['required', 'hex_color'],
        ];
    }

    /**
     * @param  Room  $model
     */
    public function load(Model $model): void
    {
        $this->editingId = $model->id;
        $this->name = $model->name;
        $this->capacity = $model->capacity;
        $this->color = $model->color;
    }

    public function save(): Room
    {
        $data = $this->validate();

        return Room::query()->updateOrCreate(['id' => $this->editingId], $data);
    }
}
