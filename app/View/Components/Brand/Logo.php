<?php

namespace App\View\Components\Brand;

use App\Enums\Locale;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Logo Éducœur : le cœur-livre et le nom.
 * Le nom arabe (et le slogan avec « tagline ») n'apparaît que sur la version arabe du site :
 * une page en français ou en anglais reste entièrement dans sa langue.
 */
class Logo extends Component
{
    private const SIZES = [
        'sm' => ['mark' => 'size-8', 'name' => 'text-xl', 'ar' => 'text-xs'],
        'md' => ['mark' => 'size-11', 'name' => 'text-2xl', 'ar' => 'text-sm'],
        'lg' => ['mark' => 'size-20', 'name' => 'text-5xl', 'ar' => 'text-xl'],
    ];

    /** @var array{mark: string, name: string, ar: string} */
    public array $classes;

    public bool $showArabic;

    public function __construct(
        string $size = 'md',
        public bool $mono = false,
        public bool $tagline = false,
    ) {
        $this->classes = self::SIZES[$size] ?? self::SIZES['md'];
        $this->showArabic = Locale::current() === Locale::Ar;
    }

    public function render(): View
    {
        return view('components.brand.logo');
    }
}
