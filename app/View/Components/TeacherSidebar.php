<?php

namespace App\View\Components;

use App\Support\TeacherNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Menu latéral de l'espace enseignant (voir App\Support\TeacherNavigation).
 */
class TeacherSidebar extends Component
{
    /** @var list<array{label: string, items: list<array{url: string, label: string, icon: string, active: bool}>}> */
    public array $sections;

    public function __construct()
    {
        $this->sections = TeacherNavigation::sections();
    }

    public function render(): View
    {
        return view('components.teacher-sidebar');
    }
}
