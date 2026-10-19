<?php

return [

    /*
    | Langues disponibles (la langue par défaut est APP_LOCALE).
    | "carbon" est la locale utilisée pour les dates (ar_DZ : mois algériens).
    */
    'locales' => [
        'fr' => ['carbon' => 'fr', 'dir' => 'ltr', 'native' => 'Français', 'short' => 'FR', 'og' => 'fr_DZ'],
        'ar' => ['carbon' => 'ar_DZ', 'dir' => 'rtl', 'native' => 'العربية', 'short' => 'ع', 'og' => 'ar_DZ'],
        'en' => ['carbon' => 'en', 'dir' => 'ltr', 'native' => 'English', 'short' => 'EN', 'og' => 'en_US'],
    ],

    'currency' => 'DA',

    // Secret partagé avec Vercel Cron (en-tête « Authorization: Bearer … »)
    'cron_secret' => env('CRON_SECRET'),

    'groups' => [
        'max_students' => 15,
    ],

    'lessons' => [
        'duration_minutes' => 120,
        // Ouverture de l'appel avant le début de la séance
        'attendance_opens_minutes_before' => 10,
        // Nombre de jours de séances générées à l'avance pour les groupes
        'generate_days_ahead' => 14,
    ],

    'payments' => [
        'default_lessons_covered' => 4,
    ],

    'contact' => [
        'phone' => env('EDUCOEUR_PHONE', '+213 555 00 00 00'),
        'whatsapp' => env('EDUCOEUR_WHATSAPP', '213555000000'),
        'email' => env('MAIL_FROM_ADDRESS', 'contact@educoeur.dz'),
        // Cité Ighil Taourga, Boumerdès
        'latitude' => 36.7525,
        'longitude' => 3.4769,
    ],

];
