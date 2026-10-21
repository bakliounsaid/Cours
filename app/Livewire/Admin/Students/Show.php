<?php

namespace App\Livewire\Admin\Students;

use App\Models\Attendance;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\AttendanceRepository;
use App\Repositories\Contracts\PaymentRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Repositories\Contracts\TeacherRepository;
use App\Services\LessonCounter;
use App\Support\Format;
use App\Support\LessonBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Fiche élève (admin) : statistiques de présence, paiements et cycle de séances par enseignant.
 */
#[Layout('components.layouts.admin')]
class Show extends Component
{
    #[Locked]
    public int $studentId;

    public function mount(Student $student): void
    {
        $this->studentId = $student->id;
    }

    public function render(): View
    {
        $student = app(StudentRepository::class)->findWithDetails($this->studentId);
        $stats = app(AttendanceRepository::class)->statsForStudent($student);
        $attended = $stats['present'] + $stats['late'];
        $total = $attended + $stats['absent'];
        $payments = app(PaymentRepository::class)->forStudent($student);
        $balances = app(LessonCounter::class)->forStudentsAllTeachers([$student->id])[$student->id] ?? [];
        $status = LessonCounter::worstStatus($balances);

        return view('livewire.admin.students.show', [
            'student' => $student,
            'initials' => $student->initials(),
            'phone' => $student->parentPhoneDisplay(),
            'phoneHref' => $student->parentPhoneHref(),
            'enrolledAt' => Format::longDate($student->enrolled_at),
            'status' => ['label' => $status->label(), 'classes' => $status->badgeClasses()],
            'groups' => $student->groups->map(fn (Group $group) => [
                'name' => $group->name,
                'subject' => $group->subject->name,
                'color' => $group->subject->color,
                'teacher' => $group->teacher->name,
            ]),
            'individualTeachers' => $student->individualTeachers->map(fn (Teacher $teacher) => $teacher->name)->implode(', '),
            'tiles' => [
                ['label' => __('admin.students.attended'), 'value' => $attended, 'icon' => 'check-circle', 'tone' => 'green'],
                ['label' => __('admin.students.late'), 'value' => $stats['late'], 'icon' => 'clock', 'tone' => 'sun'],
                ['label' => __('admin.students.absences'), 'value' => $stats['absent'], 'icon' => 'x-circle', 'tone' => 'red'],
                ['label' => __('admin.students.attendance_rate'), 'value' => $total > 0 ? round($attended / $total * 100).' %' : '—', 'icon' => 'chart', 'tone' => 'navy'],
                ['label' => __('admin.students.total_paid'), 'value' => Format::money($payments->sum('amount')), 'icon' => 'banknotes', 'tone' => 'coral'],
            ],
            'cycles' => $this->cycles($balances),
            'payments' => $payments->map(fn (Payment $payment) => [
                'date' => Format::date($payment->paid_at),
                'amount' => Format::money($payment->amount),
                'lessons' => trans_choice('admin.students.lessons_count', $payment->lessons_covered, ['count' => $payment->lessons_covered]),
                'teacher' => $payment->teacher?->name ?? '—',
                'method' => $payment->method->label(),
                'recordedBy' => $payment->recorder?->name,
            ]),
            'attendances' => app(AttendanceRepository::class)->recentForStudent($student)->map(fn (Attendance $attendance) => [
                'date' => Format::date($attendance->lesson->date),
                'time' => Format::timeRange($attendance->lesson->starts_at, $attendance->lesson->ends_at),
                'lesson' => $attendance->lesson->displayName(),
                'subject' => $attendance->lesson->subject->name,
                'teacher' => $attendance->lesson->teacher->name,
                'status' => $attendance->status->label(),
                'statusClasses' => $attendance->status->badgeClasses(),
            ]),
        ])->title($student->full_name);
    }

    /**
     * Cycle de séances en cours chez chaque enseignant.
     *
     * @param  array<int, LessonBalance>  $balances
     * @return Collection<int, array<string, mixed>>
     */
    private function cycles(array $balances): Collection
    {
        $teachers = app(TeacherRepository::class)->findManyKeyed(array_keys($balances));

        return collect($balances)->map(fn (LessonBalance $balance, int $teacherId) => [
            'teacher' => $teachers->get($teacherId)?->name ?? '—',
            'attended' => $balance->attended,
            'covered' => $balance->covered,
            'progress' => $balance->progressLabel(),
            'percent' => $balance->progressPercent(),
            'barClasses' => $balance->status()->progressClasses(),
            'status' => $balance->status()->label(),
            'statusClasses' => $balance->status()->badgeClasses(),
        ])->values();
    }
}
