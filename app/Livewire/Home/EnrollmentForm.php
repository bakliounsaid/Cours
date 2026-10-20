<?php

namespace App\Livewire\Home;

use App\Actions\Enrollment\SubmitEnrollmentRequest;
use App\Enums\Cycle;
use App\Models\Level;
use App\Rules\AlgerianPhone;
use App\Support\Catalog;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Formulaire public « Inscrire mon enfant ».
 */
class EnrollmentForm extends Component
{
    private const MAX_PER_HOUR = 5;

    public string $student_name = '';

    public string $level_id = '';

    /** @var list<string> */
    public array $subject_ids = [];

    public string $parent_phone = '';

    public string $message = '';

    /** Champ piège invisible : les robots le remplissent, les humains non. */
    public string $website = '';

    public bool $submitted = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'student_name' => ['required', 'string', 'min:3', 'max:160'],
            'level_id' => ['required', Rule::exists('levels', 'id')],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => [Rule::exists('subjects', 'id')->where('is_active', true)],
            'parent_phone' => ['required', 'string', new AlgerianPhone],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return ['subject_ids.*' => __('validation.attributes.subject_ids')];
    }

    public function updated(string $property): void
    {
        if ($property !== 'website') {
            $this->validateOnly($property);
        }
    }

    public function submit(SubmitEnrollmentRequest $submitEnrollmentRequest): void
    {
        $data = $this->validate();

        $key = 'enrollment-request:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_HOUR)) {
            $this->addError('student_name', __('home.form.too_many'));

            return;
        }

        RateLimiter::hit($key, 3600);

        if ($this->website === '') {
            $submitEnrollmentRequest->handle($data);
        }

        $this->reset('student_name', 'level_id', 'subject_ids', 'parent_phone', 'message');
        $this->submitted = true;
    }

    public function again(): void
    {
        $this->submitted = false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    #[Computed]
    public function levelOptions(): array
    {
        return Catalog::levels()
            ->groupBy(fn (Level $level) => $level->cycle->label())
            ->map(fn ($levels) => $levels->pluck('name', 'id')->all())
            ->all();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function subjectOptions(): array
    {
        return Catalog::activeSubjects()->pluck('name', 'id')->all();
    }

    public function render(): View
    {
        return view('livewire.home.enrollment-form', [
            'cycles' => Cycle::cases(),
        ]);
    }
}
