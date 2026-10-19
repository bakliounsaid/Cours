<?php

namespace App\View\Components;

use App\Support\TeacherNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Barre d'onglets du bas de l'espace enseignant.
 */
class TeacherTabBar extends Component
{
    /** @var list<array{url: string, label: string, icon: string, active: bool}> */
    public array $tabs;

    public ?int $activeIndex;

    public function __construct()
    {
        $this->tabs = TeacherNavigation::tabs();
        $this->activeIndex = TeacherNavigation::activeIndex($this->tabs);
    }

    public function render(): View
    {
        return view('components.teacher-tab-bar');
    }
}
