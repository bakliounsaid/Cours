<?php

use App\Livewire\Teacher\Groups;
use App\Livewire\Teacher\Lessons;
use App\Livewire\Teacher\Schedule;
use App\Livewire\Teacher\Students;
use App\Livewire\Teacher\Today;
use Illuminate\Support\Facades\Route;

/*
| Espace enseignant : préfixe /enseignant, noms « teacher.* », rôle enseignant obligatoire.
*/

Route::livewire('/', Today::class)->name('today');

Route::livewire('/seances', Lessons\Index::class)->name('lessons');
Route::livewire('/programme', Schedule::class)->name('schedule');

Route::livewire('/groupes', Groups\Index::class)->name('groups');
Route::livewire('/groupes/{group}', Groups\Show::class)->name('groups.show');

Route::livewire('/eleves', Students\Index::class)->name('students');

Route::livewire('/seances/{lesson}', Lessons\Show::class)->name('lessons.show');
