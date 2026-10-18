<?php

namespace App\Livewire\Teacher;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.teacher')]
class Today extends Component
{
    public function render(): View
    {
        return view('livewire.teacher.today')->title(__('teacher.nav.today'));
    }
}
