<?php

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Enums\LessonType;
use App\Enums\PaymentStatus;
use App\Livewire\Teacher\Lessons\Show;
use App\Livewire\Teacher\Students\Index;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\LessonCounter;
use App\Support\LessonBalance;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->teacher = Teacher::factory()->create();
    $this->group = Group::factory()->create(['teacher_id' => $this->teacher->id]);
    $this->student = Student::factory()->create(['first_name' => 'Yanis']);
    $this->group->students()->attach($this->student, ['joined_at' => '2026-10-01']);

    $this->actingAs($this->teacher->user);
});

/** Séance passée avec la présence de l'élève. */
function attendedLesson(Teacher $teacher, Student $student, AttendanceStatus $status = AttendanceStatus::Present, int $daysAgo = 1): Lesson
{
    static $hour = 8;
    $start = Carbon::parse('2026-10-01')->addDays($daysAgo)->setTime($hour++ % 20, 0);

    $lesson = Lesson::factory()->create([
        'teacher_id' => $teacher->id,
        'date' => $start->toDateString(),
        'starts_at' => $start,
        'ends_at' => $start->copy()->addHour(),
        'status' => LessonStatus::Done,
    ]);
    $lesson->attendances()->create(['student_id' => $student->id, 'status' => $status]);

    return $lesson;
}

function balanceOf(Teacher $teacher, Student $student): LessonBalance
{
    return app(LessonCounter::class)->forStudent($teacher, $student->id);
}

it('compte les séances suivies et signale le paiement à la 4e', function () {
    attendedLesson($this->teacher, $this->student);
    attendedLesson($this->teacher, $this->student, AttendanceStatus::Late, 2);
    attendedLesson($this->teacher, $this->student, daysAgo: 3);

    expect(balanceOf($this->teacher, $this->student)->status())->toBe(PaymentStatus::ReminderSoon)
        ->and(balanceOf($this->teacher, $this->student)->progressLabel())->toBe('3 / 4');

    attendedLesson($this->teacher, $this->student, daysAgo: 4);

    expect(balanceOf($this->teacher, $this->student)->status())->toBe(PaymentStatus::Due);

    attendedLesson($this->teacher, $this->student, daysAgo: 5);

    expect(balanceOf($this->teacher, $this->student)->status())->toBe(PaymentStatus::Overdue);
});

it('ne compte ni les absences ni les séances chez un autre enseignant', function () {
    attendedLesson($this->teacher, $this->student, AttendanceStatus::Absent);
    foreach (range(1, 4) as $day) {
        attendedLesson(Teacher::factory()->create(), $this->student, daysAgo: $day + 1);
    }

    expect(balanceOf($this->teacher, $this->student)->attended)->toBe(0)
        ->and(balanceOf($this->teacher, $this->student)->status())->toBe(PaymentStatus::UpToDate);
});

it('remet le compteur à zéro quand l’enseignant enregistre le paiement', function () {
    foreach (range(1, 4) as $day) {
        attendedLesson($this->teacher, $this->student, daysAgo: $day);
    }

    Livewire::test(Index::class)
        ->set('mode', 'payment')
        ->assertSee('Yanis')
        ->call('recordPayment', $this->student->id)
        ->assertSet('payment.lessons_covered', 4)
        ->set('payment.amount', 4000)
        ->call('savePayment')
        ->assertHasNoErrors()
        ->assertDispatched('toast', type: 'success')
        ->assertDontSee('Yanis');

    $payment = Payment::query()->sole();
    expect($payment->teacher_id)->toBe($this->teacher->id)
        ->and($payment->recorded_by)->toBe($this->teacher->user_id)
        ->and(balanceOf($this->teacher, $this->student)->status())->toBe(PaymentStatus::UpToDate);
});

it('signale le paiement dès la validation de l’appel de la 4e séance', function () {
    foreach (range(1, 3) as $day) {
        attendedLesson($this->teacher, $this->student, daysAgo: $day);
    }

    $lesson = Lesson::factory()->create([
        'group_id' => $this->group->id,
        'teacher_id' => $this->teacher->id,
        'type' => LessonType::Group,
        'date' => '2026-11-03',
        'starts_at' => '2026-11-03 17:00',
        'ends_at' => '2026-11-03 19:00',
    ]);
    $this->travelTo(Carbon::parse('2026-11-03 17:01'));

    Livewire::test(Show::class, ['lesson' => $lesson])
        ->assertSee(__('teacher.payments.alert_soon'))
        ->call('saveAttendance')
        ->assertDispatched('toast', type: 'warning')
        ->assertSee(__('teacher.payments.alert_due', ['count' => 4]));
});

it('affiche l’alerte des paiements sur l’accueil', function () {
    foreach (range(1, 4) as $day) {
        attendedLesson($this->teacher, $this->student, daysAgo: $day);
    }

    $this->get(route('teacher.today'))
        ->assertOk()
        ->assertSee(trans_choice('teacher.payments.dashboard_alert', 1, ['count' => 1]));
});
