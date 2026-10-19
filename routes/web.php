<?php

use App\Enums\Locale;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\SwitchLocaleController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Home\HomePage;
use Illuminate\Support\Facades\Route;

/*
| Page d'accueil publique : « / » (langue mémorisée) et « /fr », « /ar »
*/
Route::livewire('/', HomePage::class)->name('home');
Route::livewire('/{locale}', HomePage::class)->whereIn('locale', Locale::values())->name('home.localized');

Route::get('/langue/{locale}', SwitchLocaleController::class)
    ->whereIn('locale', Locale::values())
    ->name('locale.switch');

/*
| Tâches planifiées appelées par Vercel Cron (protégées par CRON_SECRET)
*/
Route::get('/systeme/cron/seances', [CronController::class, 'lessons'])->name('cron.lessons');

/*
| Authentification (admin et enseignants uniquement)
*/
Route::middleware('guest')->group(function () {
    Route::livewire('/connexion', Login::class)->name('login');
    Route::livewire('/admin/login', Login::class)->name('admin.login');
    Route::livewire('/enseignant/login', Login::class)->name('teacher.login');
    Route::livewire('/mot-de-passe-oublie', ForgotPassword::class)->name('password.request');
    Route::livewire('/reinitialiser-mot-de-passe/{token}', ResetPassword::class)->name('password.reset');
});

Route::post('/deconnexion', LogoutController::class)->middleware('auth')->name('logout');

/*
| Espaces privés, strictement séparés par rôle
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

Route::middleware(['auth', 'role:teacher'])
    ->prefix('enseignant')
    ->name('teacher.')
    ->group(base_path('routes/teacher.php'));
