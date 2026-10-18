<?php

use App\Models\Teacher;
use App\Models\User;

it('redirige un visiteur vers la connexion', function (string $url, string $login) {
    $this->get($url)->assertRedirect(route($login));
})->with([
    'admin' => ['/admin', 'login'],
    'enseignant' => ['/enseignant', 'teacher.login'],
]);

it('interdit /admin à un enseignant', function () {
    $teacher = Teacher::factory()->create()->user;

    $this->actingAs($teacher)->get('/admin')->assertForbidden();
    $this->actingAs($teacher)->get('/admin/salles')->assertForbidden();
});

it('interdit /enseignant à un admin', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/enseignant')->assertForbidden();
});

it('ouvre chaque espace à son rôle', function () {
    $this->actingAs(User::factory()->admin()->create())->get('/admin')->assertOk();
    $this->actingAs(Teacher::factory()->create()->user)->get('/enseignant')->assertOk();
});

it('affiche l’admin en arabe et en RTL selon la langue du compte', function () {
    $admin = User::factory()->admin()->arabic()->create();

    $this->actingAs($admin)
        ->withSession(['locale' => 'ar'])
        ->get('/admin')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl">', false)
        ->assertSee('لوحة القيادة');
});
