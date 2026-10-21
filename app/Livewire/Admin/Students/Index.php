<?php

namespace App\Livewire\Admin\Students;

use App\Enums\PaymentStatus;
use App\Livewire\Tables\Column;
use App\Livewire\Tables\DataTable;
use App\Livewire\Tables\Filter;
use App\Models\Student;
use App\Repositories\Contracts\PaymentRepository;
use App\Repositories\Contracts\StudentRepository;
use App\Services\LessonCounter;
use App\Support\Catalog;
use App\Support\Format;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;

/**
 * Élèves de l'école : séances suivies, absences, taux de présence et statut de paiement.
 */
#[Layout('components.layouts.admin')]
class Index extends DataTable
{
    /** @var array<int, PaymentStatus> statut de paiement des élèves de la page affichée */
    private array $paymentStatuses = [];

    protected function query(): Builder
    {
        return app(StudentRepository::class)->adminTableQuery();
    }

    protected function columns(): array
    {
        return [
            Column::make('last_name', __('admin.students.name'))->sortable('last_name')
                ->format(fn (Student $student) => view('livewire.admin.students.name-cell', ['student' => $student]))
                ->exportAs(fn (Student $student) => $student->full_name),
            Column::make('level', __('admin.students.level'))->hideOnMobile()
                ->format(fn (Student $student) => $student->level->name),
            Column::make('groups', __('admin.students.groups'))->hideOnMobile()
                ->format(fn (Student $student) => $student->groups->pluck('name')->implode(', ') ?: '—'),
            Column::make('attended_count', __('admin.students.attended'))->sortable(),
            Column::make('absent_count', __('admin.students.absences'))->sortable(),
            Column::make('rate', __('admin.students.attendance_rate'))
                ->format(fn (Student $student) => $this->attendanceRate($student)),
            Column::make('payment', __('admin.students.payment'))
                ->format(fn (Student $student) => view('tables.cells.badge', [
                    'color' => $this->paymentStatus($student)->badgeClasses(),
                    'label' => $this->paymentStatus($student)->label(),
                ]))
                ->exportAs(fn (Student $student) => $this->paymentStatus($student)->label()),
            Column::make('parent_phone', __('admin.students.parent_phone'))->exportOnly()
                ->exportAs(fn (Student $student) => $student->parentPhoneDisplay()),
        ];
    }

    protected function filters(): array
    {
        return [
            Filter::make('level', __('admin.students.level'), Catalog::levels()->pluck('name', 'id')->all(),
                fn (Builder $query, string $value) => $query->where('level_id', $value)),
            Filter::make('status', __('ui.status'), ['1' => __('ui.active'), '0' => __('ui.inactive')],
                fn (Builder $query, string $value) => $query->where('is_active', $value === '1')),
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $query->search($term);
    }

    protected function applyDefaultSort(Builder $query): void
    {
        $query->orderByDesc('is_active')->orderBy('last_name')->orderBy('first_name');
    }

    protected function rowActionsView(): string
    {
        return 'livewire.admin.students.row-actions';
    }

    protected function exportFileName(): string
    {
        return 'eleves';
    }

    /**
     * Statuts de paiement calculés une seule fois pour toute la page (deux requêtes groupées).
     */
    protected function rows(): LengthAwarePaginator
    {
        $rows = parent::rows();
        $this->loadPaymentStatuses($rows->getCollection()->modelKeys());

        return $rows;
    }

    protected function viewData(): array
    {
        $counter = app(LessonCounter::class);
        $balances = $counter->forStudentsAllTeachers(app(StudentRepository::class)->adminTableQuery()->active()->pluck('students.id'));
        $toPay = count(array_filter($balances, fn (array $byTeacher) => LessonCounter::worstStatus($byTeacher)->needsAttention()));

        return [
            'stats' => [
                'active' => app(StudentRepository::class)->activeCount(),
                'to_pay' => $toPay,
                'month_payments' => Format::money(app(PaymentRepository::class)->totalBetween(now()->startOfMonth(), now()->endOfMonth())),
            ],
        ];
    }

    protected function view(): string
    {
        return 'livewire.admin.students.index';
    }

    public function render(): View
    {
        return parent::render()->title(__('admin.nav.students'));
    }

    /**
     * @param  list<int>  $studentIds
     */
    private function loadPaymentStatuses(array $studentIds): void
    {
        // Sans séance ni paiement : à jour
        $this->paymentStatuses += array_fill_keys($studentIds, PaymentStatus::UpToDate);

        foreach (app(LessonCounter::class)->forStudentsAllTeachers($studentIds) as $studentId => $byTeacher) {
            $this->paymentStatuses[$studentId] = LessonCounter::worstStatus($byTeacher);
        }
    }

    private function paymentStatus(Student $student): PaymentStatus
    {
        // Export Excel : les lignes ne passent pas par rows()
        if (! array_key_exists($student->id, $this->paymentStatuses)) {
            $this->loadPaymentStatuses([$student->id]);
        }

        return $this->paymentStatuses[$student->id] ?? PaymentStatus::UpToDate;
    }

    private function attendanceRate(Student $student): string
    {
        $total = $student->attended_count + $student->absent_count;

        return $total > 0 ? round($student->attended_count / $total * 100).' %' : '—';
    }
}
