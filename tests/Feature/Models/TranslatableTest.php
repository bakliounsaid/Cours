<?php

use App\Models\Subject;

it('affiche le nom dans la langue courante', function () {
    $subject = Subject::factory()->create(['name' => ['fr' => 'Mathématiques', 'ar' => 'الرياضيات']]);

    app()->setLocale('ar');
    expect($subject->fresh()->name)->toBe('الرياضيات');

    app()->setLocale('fr');
    expect($subject->fresh()->name)->toBe('Mathématiques');
});

it('se replie sur le français quand la version arabe est vide', function () {
    $subject = Subject::factory()->create(['name' => ['fr' => 'Physique', 'ar' => '']]);

    app()->setLocale('ar');

    expect($subject->fresh()->name)->toBe('Physique');
});

it('modifie seulement la langue courante quand on affecte une chaîne', function () {
    $subject = Subject::factory()->create(['name' => ['fr' => 'Anglais', 'ar' => 'الإنجليزية']]);

    app()->setLocale('ar');
    $subject->update(['name' => 'اللغة الإنجليزية']);

    expect($subject->fresh()->getTranslations('name'))->toBe(['fr' => 'Anglais', 'ar' => 'اللغة الإنجليزية', 'en' => null]);
});

it('expose les deux versions en tableau', function () {
    $subject = Subject::factory()->create(['name' => ['fr' => 'Arabe', 'ar' => 'العربية']]);

    expect($subject->toArray()['name'])->toBe(['fr' => 'Arabe', 'ar' => 'العربية', 'en' => null]);
});
