<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Rooms;
use App\Livewire\Admin\Students;
use App\Livewire\Admin\Teachers;
use Illuminate\Support\Facades\Route;

/*
| Espace admin : préfixe /admin, noms « admin.* », rôle admin obligatoire.
*/

Route::livewire('/', Dashboard::class)->name('dashboard');

Route::livewire('/eleves', Students\Index::class)->name('students.index');
Route::livewire('/eleves/{student}', Students\Show::class)->name('students.show');

Route::livewire('/enseignants', Teachers\Index::class)->name('teachers');
Route::livewire('/enseignants/{teacher}', Teachers\Show::class)->name('teachers.show');

Route::livewire('/salles', Rooms\Index::class)->name('rooms');
