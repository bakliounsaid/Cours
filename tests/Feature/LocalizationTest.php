<?php

use App\Support\Format;

it('affiche la page d’accueil en français par défaut', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('<html lang="fr" dir="ltr">', false)
        ->assertSee('Enseigner avec cœur');
});

it('affiche la version arabe en RTL complet', function () {
    $this->get('/ar')
        ->assertOk()
        ->assertSee('<html lang="ar" dir="rtl">', false)
        ->assertSee('نُعلّم بقلب');
});

it('mémorise la langue choisie dans la session', function () {
    $this->get('/ar')->assertSessionHas('locale', 'ar');

    $this->get('/')->assertSee('dir="rtl"', false);
});

it('bascule de langue et revient sur la page d’accueil localisée', function () {
    $this->from('/fr')
        ->get(route('locale.switch', 'ar'))
        ->assertRedirect(route('home.localized', 'ar'))
        ->assertSessionHas('locale', 'ar');
});

it('refuse une langue inconnue', function () {
    $this->get('/langue/en')->assertNotFound();
});

it('formate les dates dans la langue courante avec des chiffres occidentaux', function () {
    app()->setLocale('fr');
    expect(Format::dayDate('2026-10-20'))->toBe('mardi 20 octobre');

    app()->setLocale('ar');
    expect(Format::dayDate('2026-10-20'))->toBe('الثلاثاء 20 أكتوبر')
        ->and(Format::money(2500))->toBe('2 500 دج');
});
