<?php

use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Livewire\Teacher\Lessons\Index;
use App\Livewire\Teacher\Lessons\Show;
use App\Livewire\Teacher\Schedule;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\PaymentDueNotification;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // Mardi 3 novembre 2026, 10 h
    $this->travelTo(Carbon::parse('2026-11-03 10:00'));
    $this->teacher = Teacher::factory()->create();
    $this->group = Group::factory()->create(['teacher_id' => $this->teacher->id, 'name' => 'Maths 4AM']);
    $this->actingAs($this->teacher->user);
});

function lessonAt(string $start, array $attributes = []): Lesson
{
    $start = Carbon::parse($start);

    return Lesson::factory()->create([
        'group_id' => test()->group->id,
        'teacher_id' => test()->teacher->id,
        'type' => LessonType::Group,
        'date' => $start->toDateString(),
        'starts_at' => $start,
        'ends_at' => $start->copy()->addHours(2),
        ...$attributes,
    ]);
}

it('affiche le menu latéral avec les pages séparées', function () {
    $this->get(route('teacher.today'))
        ->assertOk()
        ->assertSee(route('teacher.lessons'))
        ->assertSee(route('teacher.schedule'))
        ->assertSee(route('teacher.groups'))
        ->assertSee(route('teacher.students'))
        ->assertSee(__('teacher.nav.dashboard'));
});

it('sépare les séances à venir, passées et annulées', function () {
    lessonAt('2026-11-04 17:00', ['title' => 'Séance à venir']);
    lessonAt('2026-11-01 17:00', ['title' => 'Séance passée', 'status' => LessonStatus::Done]);
    lessonAt('2026-11-05 17:00', ['title' => 'Séance annulée', 'status' => LessonStatus::Cancelled]);

    Livewire::test(Index::class)
        ->assertSee('Séance à venir')->assertDontSee('Séance passée')->assertDontSee('Séance annulée')
        ->set('tab', 'past')
        ->assertSee('Séance passée')->assertDontSee('Séance à venir')
        ->set('tab', 'cancelled')
        ->assertSee('Séance annulée')->assertDontSee('Séance passée');
});

it('affiche le programme de la semaine et navigue de semaine en semaine', function () {
    lessonAt('2026-11-04 17:00', ['title' => 'Séance de mercredi']);
    lessonAt('2026-11-11 17:00', ['title' => 'Semaine prochaine']);

    Livewire::test(Schedule::class)
        ->assertSee('Séance de mercredi')->assertDontSee('Semaine prochaine')
        ->call('nextWeek')
        ->assertSee('Semaine prochaine')->assertDontSee('Séance de mercredi');
});

it('notifie l’enseignant une seule fois quand un élève atteint son cycle', function () {
    $student = Student::factory()->create();
    $this->group->students()->attach($student, ['joined_at' => '2026-10-01']);

    // 3 séances déjà suivies
    foreach (['2026-10-25', '2026-10-27', '2026-11-01'] as $date) {
        lessonAt("{$date} 17:00", ['status' => LessonStatus::Done])
            ->attendances()->create(['student_id' => $student->id, 'status' => 'present']);
    }

    $lesson = lessonAt('2026-11-03 17:00');
    $this->travelTo(Carbon::parse('2026-11-03 17:02'));

    $component = Livewire::test(Show::class, ['lesson' => $lesson])->call('saveAttendance');
    // Une correction le même jour ne renvoie pas d'alerte
    $component->call('saveAttendance');

    $notifications = $this->teacher->user->notifications;
    expect($notifications)->toHaveCount(1)
        ->and($notifications->first()->type)->toBe(PaymentDueNotification::class)
        ->and($notifications->first()->data['key'])->toBe('notifications.payment_due')
        ->and($notifications->first()->data['params']['count'])->toBe(4);

    $this->get(route('teacher.today'))->assertSee(__('notifications.payment_due', ['name' => $student->full_name, 'count' => 4]));
});
