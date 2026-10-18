<?php

use Illuminate\Support\Facades\Schedule;

/*
| Tâches planifiées (lancées par « php artisan schedule:work », service « scheduler » de Docker)
*/

// Séances des groupes : toujours deux semaines d'avance
Schedule::command('lessons:generate')->dailyAt('00:15')->withoutOverlapping()->onOneServer();
