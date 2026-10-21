<?php

namespace App\Livewire\Teacher;

use App\Livewire\Teacher\Concerns\ForCurrentTeacher;
use App\Livewire\Teacher\Concerns\WithWeekPlanning;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Mon programme : mes séances de la semaine (dimanche → samedi).
 */
#[Layout('components.layouts.teacher')]
class Schedule extends Component
{
    use ForCurrentTeacher, WithWeekPlanning;

    public function render(): View
    {
        return view('livewire.teacher.schedule', [
            'planning' => $this->planning(),
        ])->title(__('teacher.nav.schedule'));
    }
}
