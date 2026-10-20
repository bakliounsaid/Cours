<?php

namespace App\Livewire\Forms;

use App\Actions\Payments\RecordPayment;
use App\Enums\PaymentMethod;
use App\Models\Payment;
use App\Models\Teacher;
use App\Repositories\Contracts\StudentRepository;
use App\Settings\Settings;
use Illuminate\Validation\Rule;
use Livewire\Form;

/**
 * Enregistrement d'un paiement reçu par l'enseignant connecté.
 */
class PaymentForm extends Form
{
    public ?int $student_id = null;

    public int|string $amount = '';

    public string $method = 'cash';

    public int|string $lessons_covered = 4;

    public string $notes = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1', 'max:1000000'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'lessons_covered' => ['required', 'integer', 'min:1', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function start(int $studentId): void
    {
        $this->reset();
        $this->resetErrorBag();
        $this->student_id = $studentId;
        $this->lessons_covered = app(Settings::class)->lessonsPerCycle();
    }

    public function save(): Payment
    {
        $data = $this->validate();
        $student = app(StudentRepository::class)->findForTeacher($this->teacher(), $this->student_id);

        return app(RecordPayment::class)->handle($this->teacher(), $student, $data, auth()->user());
    }

    private function teacher(): Teacher
    {
        return auth()->user()->teacher;
    }
}
