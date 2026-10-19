<?php

use App\Livewire\Auth\Login;
use App\Models\Teacher;
use App\Models\User;
use Livewire\Livewire;

it('connecte un admin et l’envoie au tableau de bord', function () {
    $admin = User::factory()->admin()->create();

    Livewire::test(Login::class)
        ->set('form.email', $admin->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('connecte un enseignant et l’envoie à sa journée', function () {
    $teacher = Teacher::factory()->create()->user;

    Livewire::test(Login::class)
        ->set('form.email', $teacher->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertRedirect(route('teacher.today'));
});

it('ne renvoie jamais un enseignant vers une page admin demandée avant connexion', function () {
    $teacher = Teacher::factory()->create()->user;
    session()->put('url.intended', url('/admin/salles'));

    Livewire::test(Login::class)
        ->set('form.email', $teacher->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertRedirect(route('teacher.today'));
});

it('garde la langue de la session après la connexion', function () {
    $admin = User::factory()->admin()->create();
    session()->put('locale', 'ar');

    Livewire::test(Login::class)
        ->set('form.email', $admin->email)
        ->set('form.password', 'password')
        ->call('login');

    expect(session('locale'))->toBe('ar');
});

it('refuse un mauvais mot de passe avec un message traduit', function () {
    $admin = User::factory()->admin()->create();

    Livewire::test(Login::class)
        ->set('form.email', $admin->email)
        ->set('form.password', 'mauvais')
        ->call('login')
        ->assertHasErrors(['form.email' => __('auth.failed')]);

    $this->assertGuest();
});

it('garde la langue choisie en session uniquement', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->from('/admin')->get(route('locale.switch', 'en'))
        ->assertRedirect('/admin')
        ->assertSessionHas('locale', 'en');

    $this->get('/admin')->assertSee('<html lang="en" dir="ltr">', false);
});
