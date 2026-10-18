<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class ForgotPassword extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status !== Password::RESET_LINK_SENT && $status !== Password::INVALID_USER) {
            $this->addError('email', __($status));

            return;
        }

        // Même message que le compte existe ou non (pas d'énumération des comptes).
        $this->reset('email');
        $this->status = __(Password::RESET_LINK_SENT);
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password')->title(__('auth.forgot_title'));
    }
}
