<?php

return [
    'user_role' => [
        'admin' => 'Administrateur',
        'teacher' => 'Enseignant',
    ],
    'cycle' => [
        'primary' => 'Primaire',
        'middle' => 'CEM',
    ],
    'lesson_type' => [
        'group' => 'Groupe',
        'individual' => 'Individuel',
        'special' => 'Spéciale',
        'replacement' => 'Remplacement',
        'makeup' => 'Rattrapage',
    ],
    'lesson_status' => [
        'scheduled' => 'Prévue',
        'done' => 'Faite',
        'cancelled' => 'Annulée',
        'needs_action' => 'À traiter',
    ],
    'attendance_status' => [
        'present' => 'Présent',
        'absent' => 'Absent',
        'late' => 'En retard',
    ],
    'payment_method' => [
        'cash' => 'Espèces',
        'transfer' => 'Virement',
        'other' => 'Autre',
    ],
    'payment_status' => [
        'up_to_date' => 'À jour',
        'reminder_soon' => 'Bientôt à payer',
        'due' => 'Paiement à prévoir',
        'overdue' => 'En retard',
    ],
    'enrollment_status' => [
        'new' => 'Nouvelle',
        'processed' => 'Traitée',
    ],
    'absence_scope' => [
        'lesson' => 'Une séance',
        'day' => 'Toute la journée',
    ],
    'absence_resolution' => [
        'cancelled' => 'Annulée',
        'replaced' => 'Remplacée',
        'rescheduled' => 'Reportée',
    ],
    'message_channel' => [
        'sms' => 'SMS',
        'whatsapp' => 'WhatsApp',
    ],
    'parent_message_type' => [
        'lesson_cancelled' => 'Séance annulée',
        'lesson_rescheduled' => 'Séance reportée',
        'lesson_added' => 'Séance ajoutée',
        'payment_reminder' => 'Rappel de paiement',
    ],
    'lesson_event_type' => [
        'checked_in' => 'Appel validé et pointage',
        'attendance_updated' => 'Présences corrigées',
        'work_recorded' => 'Travail réalisé enregistré',
    ],
    'locale' => [
        'fr' => 'Français',
        'ar' => 'Arabe',
        'en' => 'Anglais',
    ],
];
