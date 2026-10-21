<?php

use App\Enums\AttendanceStatus;
use App\Enums\LessonStatus;
use App\Livewire\Admin\Students\Index;
use App\Livewire\Admin\Students\Show;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-03 10:00'));
    $this->actingAs(User::factory()->admin()->create());

    $this->teacher = Teacher::factory()->create();
    $this->yanis = Student::factory()->create(['first_name' => 'Yanis', 'last_name' => 'Benali']);
    $this->lina = Student::factory()->create(['first_name' => 'Lina', 'last_name' => 'Kaci']);
});

/** Séance faite chez l'enseignant avec la présence d'un élève. */
function attendance(Teacher $teacher, Student $student, AttendanceStatus $status, int $daysAgo): void
{
    static $hour = 8;
    $start = Carbon::parse('2026-10-01')->addDays($daysAgo)->setTime($hour++ % 20, 0);

    Lesson::factory()->create([
        'teacher_id' => $teacher->id,
        'date' => $start->toDateString(),
        'starts_at' => $start,
        'ends_at' => $start->copy()->addHour(),
        'status' => LessonStatus::Done,
    ])->attendances()->create(['student_id' => $student->id, 'status' => $status]);
}

it('liste les élèves avec leurs présences et leur statut de paiement', function () {
    foreach (range(1, 4) as $day) {
        attendance($this->teacher, $this->yanis, AttendanceStatus::Present, $day);
    }
    attendance($this->teacher, $this->yanis, AttendanceStatus::Absent, 5);

    $this->get(route('admin.students.index'))->assertOk();

    Livewire::test(Index::class)
        ->assertSee('Yanis Benali')
        ->assertSee('Lina Kaci')
        ->assertSee('80 %')
        ->assertSee(__('enums.payment_status.due'))
        ->set('search', 'lina')
        ->assertSee('Lina Kaci')
        ->assertDontSee('Yanis Benali');
});

it('affiche la fiche élève : statistiques, cycle par enseignant et paiements', function () {
    foreach (range(1, 5) as $day) {
        attendance($this->teacher, $this->yanis, AttendanceStatus::Present, $day);
    }
    attendance($this->teacher, $this->yanis, AttendanceStatus::Late, 6);
    Payment::factory()->create([
        'student_id' => $this->yanis->id,
        'teacher_id' => $this->teacher->id,
        'amount' => 4000,
        'lessons_covered' => 4,
        'paid_at' => '2026-10-20 18:00',
    ]);

    $this->get(route('admin.students.show', $this->yanis))
        ->assertOk()
        ->assertSee('Yanis Benali')
        ->assertSee($this->teacher->name)
        ->assertSee('2 / 4')
        ->assertSee('4 000')
        ->assertSee(__('admin.students.cycle_detail', ['attended' => 6, 'covered' => 4]));

    Livewire::test(Show::class, ['student' => $this->yanis])
        ->assertSee('100 %')
        ->assertSee(__('enums.attendance_status.late'));
});

it('réserve la section élèves à l’admin', function () {
    $this->actingAs(Teacher::factory()->create()->user)
        ->get(route('admin.students.index'))
        ->assertForbidden();
});
