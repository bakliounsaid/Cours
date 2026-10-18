<?php

namespace App\Livewire\Admin\Teachers;

use App\Enums\LessonStatus;
use App\Livewire\Concerns\WithModalForm;
use App\Livewire\Forms\TeacherForm;
use App\Livewire\Tables\Column;
use App\Livewire\Tables\DataTable;
use App\Livewire\Tables\Filter;
use App\Models\Teacher;
use App\Support\Catalog;
use App\Support\Format;
use App\Support\Phone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.admin')]
class Index extends DataTable
{
    use WithModalForm;

    public TeacherForm $form;

    protected function query(): Builder
    {
        return Teacher::query()
            ->select('teachers.*')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->with(['user', 'subjects'])
            ->withCount(['lessons as lessons_this_month' => fn (Builder $query) => $query
                ->where('status', '!=', LessonStatus::Cancelled)
                ->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])]);
    }

    protected function columns(): array
    {
        return [
            Column::make('users.name', __('admin.teachers.name'))->sortable('users.name')
                ->format(fn (Teacher $teacher) => view('livewire.admin.teachers.name-cell', ['teacher' => $teacher]))
                ->exportAs(fn (Teacher $teacher) => $teacher->user->name),
            Column::make('email', __('admin.teachers.email'))->exportOnly()
                ->exportAs(fn (Teacher $teacher) => $teacher->user->email),
            Column::make('phone', __('admin.teachers.phone'))
                ->classes('whitespace-nowrap')
                ->format(fn (Teacher $teacher) => $teacher->phone ? Phone::format($teacher->phone) : '—'),
            Column::make('subjects', __('admin.teachers.subjects'))
                ->format(fn (Teacher $teacher) => $teacher->subjects->pluck('name')->implode(', ')),
            Column::make('rate_per_lesson', __('admin.teachers.rate'))->sortable()
                ->format(fn (Teacher $teacher) => Format::money($teacher->rate_per_lesson)),
            Column::make('lessons_this_month', __('admin.teachers.lessons_this_month'))->sortable(),
            Column::make('is_active', __('ui.status'))->sortable('teachers.is_active')
                ->format(fn (Teacher $teacher) => view('tables.cells.badge', [
                    'color' => $teacher->is_active ? 'bg-olive-50 text-olive-700' : 'bg-sand-200 text-muted',
                    'label' => $teacher->is_active ? __('ui.active') : __('ui.inactive'),
                ]))
                ->exportAs(fn (Teacher $teacher) => $teacher->is_active ? __('ui.active') : __('ui.inactive')),
        ];
    }

    protected function filters(): array
    {
        return [
            Filter::make('subject', __('admin.teachers.subject'), Catalog::activeSubjects()->pluck('name', 'id')->all(),
                fn (Builder $query, string $value) => $query->whereHas('subjects', fn (Builder $subjects) => $subjects->whereKey($value))),
            Filter::make('status', __('ui.status'), ['1' => __('ui.active'), '0' => __('ui.inactive')],
                fn (Builder $query, string $value) => $query->where('teachers.is_active', $value === '1')),
        ];
    }

    protected function applySearch(Builder $query, string $term): void
    {
        $digits = preg_replace('/\D/', '', $term);

        $query->where(function (Builder $query) use ($term, $digits) {
            $query->whereLike('users.name', "%{$term}%")
                ->orWhereLike('users.email', "%{$term}%")
                ->when($digits !== '', fn (Builder $query) => $query->orWhereLike('teachers.phone', "%{$digits}%"));
        });
    }

    protected function applyDefaultSort(Builder $query): void
    {
        $query->orderByDesc('teachers.is_active')->orderBy('users.name');
    }

    protected function rowActionsView(): string
    {
        return 'livewire.admin.teachers.row-actions';
    }

    protected function exportFileName(): string
    {
        return 'enseignants';
    }

    protected function findModel(int $id): Teacher
    {
        return Teacher::query()->with('user', 'subjects')->findOrFail($id);
    }

    /**
     * Supprimer l'enseignant supprime aussi son compte de connexion.
     *
     * @param  Teacher  $model
     */
    protected function performDelete(Model $model): void
    {
        $model->user->delete();
    }

    protected function viewData(): array
    {
        return [
            'subjectOptions' => Catalog::activeSubjects()->pluck('name', 'id')->all(),
        ];
    }

    protected function view(): string
    {
        return 'livewire.admin.teachers.index';
    }

    public function render(): View
    {
        return parent::render()->title(__('admin.nav.teachers'));
    }
}
