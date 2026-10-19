<?php

/*
| Point d'entrée Vercel (runtime vercel-php).
| Le disque des fonctions est en lecture seule, sauf /tmp : on y place le dossier storage.
*/

$storage = '/tmp/storage';

foreach (['app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    if (! is_dir("{$storage}/{$directory}")) {
        mkdir("{$storage}/{$directory}", 0755, true);
    }
}

$_ENV['LARAVEL_STORAGE_PATH'] = $_SERVER['LARAVEL_STORAGE_PATH'] = $storage;

require __DIR__.'/../public/index.php';
