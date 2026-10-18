<?php

namespace App\Livewire\Forms;

use App\Actions\Teachers\SaveTeacher;
use App\Enums\Locale;
use App\Models\Teacher;
use App\Rules\AlgerianPhone;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeacherForm extends ModelForm
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $phone = '';

    public string $locale = 'fr';

    public int|string $rate_per_lesson = 2000;

    public bool $is_active = true;

    /** @var list<string> */
    public array $subject_ids = [];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $userId = $this->editingId ? Teacher::query()->whereKey($this->editingId)->value('user_id') : null;

        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($userId)],
            'password' => [$this->isEditing() ? 'nullable' : 'required', 'string', Password::min(8)],
            'phone' => ['nullable', 'string', new AlgerianPhone],
            'locale' => ['required', Rule::enum(Locale::class)],
            'rate_per_lesson' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => [Rule::exists('subjects', 'id')],
        ];
    }

    /**
     * @param  Teacher  $model
     */
    public function load(Model $model): void
    {
        $this->editingId = $model->id;
        $this->name = $model->user->name;
        $this->email = $model->user->email;
        $this->password = '';
        $this->phone = $model->phone ? Phone::format($model->phone) : '';
        $this->locale = $model->user->locale->value;
        $this->rate_per_lesson = $model->rate_per_lesson;
        $this->is_active = $model->is_active;
        $this->subject_ids = $model->subjects->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function save(): Teacher
    {
        $data = $this->validate();

        $teacher = $this->editingId ? Teacher::query()->with('user')->findOrFail($this->editingId) : null;

        return app(SaveTeacher::class)->handle($data, $teacher);
    }
}
