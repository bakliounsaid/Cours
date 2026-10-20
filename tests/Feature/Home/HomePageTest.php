<?php

use App\Models\Subject;
use App\Settings\Settings;
use Database\Seeders\ReferenceDataSeeder;

beforeEach(fn () => $this->seed(ReferenceDataSeeder::class));

it('affiche toutes les sections en français', function () {
    $this->get('/fr')
        ->assertOk()
        ->assertSee('Chaque enfant mérite qu’on croie en lui')
        ->assertSeeInOrder(['Pourquoi Éducœur', 'Mathématiques', 'Physique', 'Arabe', 'Français', 'Anglais', 'Primaire', 'CEM', 'Cours en groupe', 'Cours individuels'])
        ->assertSee('Cité Ighil Taourga, Boumerdès')
        ->assertSee(route('teacher.login'));
});

it('affiche la version arabe avec les noms de matières arabes', function () {
    $this->get('/ar')
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('كل طفل يستحق من يؤمن به')
        ->assertSee('الرياضيات')
        ->assertDontSee('إغيل تاورقة');
});

it('raconte l’histoire de l’école avec ses trois missions', function () {
    $this->get('/fr')
        ->assertSee('Notre histoire')
        ->assertSee('2019')
        ->assertSee('fondée en 2019 par Essaid Baklioun', false)
        ->assertSeeInOrder(['Aider les familles modestes', 'Orienter la nouvelle génération', 'Réussir ensemble']);

    $this->get('/ar')
        ->assertSee('قصتنا')
        ->assertSee('تأسست مدرسة إديكور سنة 2019 على يد السعيد بكليون')
        ->assertSee('النجاح معًا');
});

it('affiche le récit modifié par l’admin', function () {
    app(Settings::class)->set('home.story', ['fr' => 'Une nouvelle histoire.', 'ar' => 'قصة جديدة.']);

    $this->get('/fr')->assertSee('Une nouvelle histoire.');
    $this->get('/ar')->assertSee('قصة جديدة.');
});

it('met à jour la page dès qu’une matière est modifiée malgré le cache', function () {
    $this->get('/fr')->assertSee('Mathématiques');

    Subject::query()->where('slug', 'maths')->first()->update(['name' => ['fr' => 'Maths et logique', 'ar' => 'الرياضيات']]);

    $this->get('/fr')->assertSee('Maths et logique')->assertDontSee('Mathématiques');
});

it('calcule les horaires à partir des créneaux', function () {
    $this->get('/fr')
        ->assertSee('Dimanche – Jeudi')
        ->assertSee('17:00 – 19:00')
        ->assertSee('08:00 – 12:00')
        ->assertSee('13:30 – 17:30')
        ->assertSee('08:00 – 18:30')
        ->assertSee('pause de 30 min à midi');
});

it('publie les balises SEO, hreflang et Open Graph', function () {
    $this->get('/ar')
        ->assertSee('<link rel="canonical" href="'.route('home.localized', 'ar').'">', false)
        ->assertSee('hreflang="fr" href="'.route('home.localized', 'fr').'"', false)
        ->assertSee('hreflang="x-default"', false)
        ->assertSee('og:image', false)
        ->assertSee('"@type":"EducationalOrganization"', false);
});

it('affiche les textes modifiés par l’admin, avec repli sur le français', function () {
    app(Settings::class)->set('home.hero_title', ['fr' => 'Bienvenue chez nous', 'ar' => '']);

    $this->get('/ar')->assertSee('Bienvenue chez nous');
});

it('affiche la page française entièrement en français, sans lieu ni nombre de salles', function () {
    $html = $this->get('/fr')->assertOk()->assertSee(__('home.gallery.title'))->getContent();
    $visible = strip_tags(preg_replace('/<(script|style)\b.*?<\/\1>/s', '', $html));

    // Seul le sélecteur de langue garde les noms natifs (« العربية »), par convention.
    expect($visible)->not->toContain('إديكور')
        ->and($visible)->not->toContain('نُعلّم بقلب')
        ->and($visible)->not->toContain('Ighil')
        ->and($visible)->not->toContain('Boumerdès')
        ->and($visible)->not->toContain('Salle Portail');
});

it('affiche la page anglaise sans texte français ni arabe dans le hero', function () {
    $this->get('/en')
        ->assertOk()
        ->assertSee('Teaching from the heart')
        ->assertDontSee('Enseigner avec cœur')
        ->assertDontSee('نُعلّم بقلب');
});
