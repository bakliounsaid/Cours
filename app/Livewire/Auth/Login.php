<?php

namespace App\Livewire\Auth;

use App\Enums\UserRole;
use App\Livewire\Forms\LoginForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public LoginForm $form;

    /** Connexion depuis le lien « Espace enseignant ». */
    public bool $teacherSpace = false;

    public function mount(): void
    {
        $this->teacherSpace = request()->routeIs('teacher.login');
    }

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        session()->regenerate();

        $user = Auth::user();
        session()->put('locale', $user->preferredLocale());

        $home = route($user->role->homeRoute());
        $intended = (string) session()->pull('url.intended', $home);

        // Un enseignant n'est jamais renvoyé vers /admin, et inversement.
        $allowedPrefix = $user->role === UserRole::Admin ? url('/admin') : url('/enseignant');

        $this->redirect(Str::startsWith($intended, $allowedPrefix) ? $intended : $home, navigate: false);
    }

    public function render(): View
    {
        return view('livewire.auth.login')
            ->title($this->teacherSpace ? __('auth.teacher_login_title') : __('auth.login_title'));
    }
}
