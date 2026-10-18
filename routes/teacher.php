<?php

use App\Livewire\Teacher\Today;
use Illuminate\Support\Facades\Route;

/*
| Espace enseignant : préfixe /enseignant, noms « teacher.* », rôle enseignant obligatoire.
*/

Route::livewire('/', Today::class)->name('today');
