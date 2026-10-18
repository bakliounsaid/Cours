<?php

use App\Enums\EnrollmentStatus;
use App\Livewire\Home\EnrollmentForm;
use App\Models\EnrollmentRequest;
use App\Models\Level;
use App\Models\Subject;
use Database\Seeders\ReferenceDataSeeder;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(ReferenceDataSeeder::class));

function fillEnrollment(): Testable
{
    return Livewire::test(EnrollmentForm::class)
        ->set('student_name', 'Yanis Benali')
        ->set('level_id', (string) Level::first()->id)
        ->set('subject_ids', [(string) Subject::first()->id])
        ->set('parent_phone', '+213 555 12 34 56')
        ->set('parent_locale', 'ar')
        ->set('message', 'Disponible le samedi');
}

it('enregistre une demande d’inscription visible dans l’admin', function () {
    fillEnrollment()->call('submit')->assertHasNoErrors()->assertSet('submitted', true);

    $request = EnrollmentRequest::sole();
    expect($request->student_name)->toBe('Yanis Benali')
        ->and($request->parent_phone)->toBe('0555123456')
        ->and($request->parent_locale->value)->toBe('ar')
        ->and($request->status)->toBe(EnrollmentStatus::New);
});

it('valide en temps réel et refuse un numéro non algérien', function () {
    Livewire::test(EnrollmentForm::class)
        ->set('parent_phone', '0123')
        ->assertHasErrors(['parent_phone'])
        ->set('parent_phone', '0661 23 45 67')
        ->assertHasNoErrors(['parent_phone']);
});

it('exige au moins une matière et un niveau', function () {
    Livewire::test(EnrollmentForm::class)
        ->set('student_name', 'Lina')
        ->call('submit')
        ->assertHasErrors(['level_id' => 'required', 'subject_ids' => 'required', 'parent_phone' => 'required']);
});

it('traduit les messages de validation en arabe', function () {
    app()->setLocale('ar');

    Livewire::test(EnrollmentForm::class)
        ->call('submit')
        ->assertSee('حقل اسم التلميذ مطلوب.');
});

it('ignore silencieusement les robots (champ piège)', function () {
    fillEnrollment()->set('website', 'http://spam.example')->call('submit')->assertSet('submitted', true);

    expect(EnrollmentRequest::count())->toBe(0);
});

it('limite le nombre de demandes par heure', function () {
    foreach (range(1, 5) as $i) {
        fillEnrollment()->call('submit');
    }

    fillEnrollment()->call('submit')->assertHasErrors('student_name');
    expect(EnrollmentRequest::count())->toBe(5);
});
