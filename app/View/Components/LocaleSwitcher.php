<?php

namespace App\View\Components;

use App\Enums\Locale;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Menu de choix de la langue (gardée en session uniquement).
 */
class LocaleSwitcher extends Component
{
    public Locale $current;

    /** @var list<Locale> */
    public array $locales;

    public function __construct()
    {
        $this->current = Locale::current();
        $this->locales = Locale::cases();
    }

    public function render(): View
    {
        return view('components.locale-switcher');
    }
}
