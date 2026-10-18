<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Teachers\Index;
use App\Livewire\Auth\Login;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
    $this->maths = Subject::factory()->create(['name' => ['fr' => 'Mathématiques', 'ar' => 'الرياضيات']]);
});

function teacherForm(): Testable
{
    return Livewire::test(Index::class)
        ->call('create')
        ->set('form.name', 'Karim Benali')
        ->set('form.email', 'karim@educoeur.dz')
        ->set('form.password', 'secret-123')
        ->set('form.phone', '0555 12 34 56')
        ->set('form.locale', 'ar')
        ->set('form.rate_per_lesson', 2000)
        ->set('form.subject_ids', [(string) test()->maths->id]);
}

it('crée un compte enseignant qui peut se connecter', function () {
    teacherForm()->call('save')->assertHasNoErrors()->assertSet('showForm', false);

    $user = User::query()->where('email', 'karim@educoeur.dz')->sole();
    expect($user->role)->toBe(UserRole::Teacher)
        ->and($user->locale->value)->toBe('ar')
        ->and(Hash::check('secret-123', $user->password))->toBeTrue()
        ->and($user->teacher->phone)->toBe('0555123456')
        ->and($user->teacher->rate_per_lesson)->toBe(2000)
        ->and($user->teacher->subjects->pluck('id')->all())->toBe([$this->maths->id]);

    auth()->logout();

    Livewire::test(Login::class)
        ->set('form.email', 'karim@educoeur.dz')
        ->set('form.password', 'secret-123')
        ->call('login')
        ->assertRedirect(route('teacher.today'));
});

it('exige un mot de passe et un e-mail unique à la création', function () {
    User::factory()->create(['email' => 'pris@educoeur.dz']);

    teacherForm()
        ->set('form.email', 'pris@educoeur.dz')
        ->set('form.password', '')
        ->call('save')
        ->assertHasErrors(['form.email' => 'unique', 'form.password' => 'required']);
});

it('garde le mot de passe quand le champ reste vide en modification', function () {
    $teacher = Teacher::factory()->create();
    $hash = $teacher->user->password;

    Livewire::test(Index::class)
        ->call('edit', $teacher->id)
        ->assertSet('form.email', $teacher->user->email)
        ->set('form.name', 'Nouveau Nom')
        ->set('form.subject_ids', [(string) $this->maths->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($teacher->user->fresh()->name)->toBe('Nouveau Nom')
        ->and($teacher->user->fresh()->password)->toBe($hash);
});

it('empêche un enseignant désactivé de se connecter', function () {
    $teacher = Teacher::factory()->create(['is_active' => false]);
    auth()->logout();

    Livewire::test(Login::class)
        ->set('form.email', $teacher->user->email)
        ->set('form.password', 'password')
        ->call('login')
        ->assertHasErrors(['form.email' => __('auth.inactive')]);

    $this->assertGuest();
});

it('déconnecte un enseignant désactivé pendant sa session', function () {
    $teacher = Teacher::factory()->create();
    $teacher->update(['is_active' => false]);

    $this->actingAs($teacher->user)->get('/enseignant')->assertRedirect(route('teacher.login'));
    $this->assertGuest();
});

it('supprime le compte de connexion avec l’enseignant', function () {
    $teacher = Teacher::factory()->create();

    Livewire::test(Index::class)->call('delete', $teacher->id)->assertDispatched('toast', type: 'success');

    expect(User::query()->whereKey($teacher->user_id)->exists())->toBeFalse()
        ->and(Teacher::query()->whereKey($teacher->id)->exists())->toBeFalse();
});

it('refuse de supprimer un enseignant qui a des séances', function () {
    $lesson = Lesson::factory()->create();

    Livewire::test(Index::class)->call('delete', $lesson->teacher_id)->assertDispatched('toast', type: 'error');

    expect(Teacher::query()->whereKey($lesson->teacher_id)->exists())->toBeTrue();
});

it('recherche un enseignant par nom ou téléphone', function () {
    Teacher::factory()->for(User::factory()->teacher()->state(['name' => 'Amina Haddad']))->create(['phone' => '0661234567']);
    Teacher::factory()->for(User::factory()->teacher()->state(['name' => 'Yacine Boudiaf']))->create();

    Livewire::test(Index::class)
        ->set('search', 'amina')->assertSee('Amina Haddad')->assertDontSee('Yacine Boudiaf')
        ->set('search', '0661 23')->assertSee('Amina Haddad')->assertDontSee('Yacine Boudiaf');
});
