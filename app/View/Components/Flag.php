<?php

namespace App\View\Components;

use App\Enums\Locale;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

/**
 * Drapeau d'une langue en SVG intégré (les emojis drapeaux ne s'affichent pas sous Windows) :
 * fr → France, ar → Algérie, en → Royaume-Uni.
 */
class Flag extends Component
{
    private const VIEW_BOXES = [
        'fr' => '0 0 3 2',
        'ar' => '0 0 30 20',
        'en' => '0 0 60 30',
    ];

    /** Identifiant unique du masque / découpage SVG (plusieurs drapeaux par page). */
    public string $svgId;

    public string $code;

    public string $viewBox;

    public function __construct(Locale $locale)
    {
        $this->code = $locale->value;
        $this->viewBox = self::VIEW_BOXES[$locale->value];
        $this->svgId = 'flag-'.Str::random(6);
    }

    public function render(): View
    {
        return view('components.flag');
    }
}
