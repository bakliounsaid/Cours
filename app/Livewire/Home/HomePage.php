<?php

namespace App\Livewire\Home;

use App\Enums\Cycle;
use App\Enums\Locale;
use App\Models\Level;
use App\Settings\Settings;
use App\Support\Catalog;
use App\Support\OpeningHours;
use App\Support\Phone;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class HomePage extends Component
{
    public function render(Settings $settings): View
    {
        $locale = Locale::current();

        $features = collect($settings->get('home.features', []))->map(fn (array $feature) => [
            'icon' => $feature['icon'] ?? 'star',
            'title' => $this->pick($feature['title'] ?? [], $locale),
            'text' => $this->pick($feature['text'] ?? [], $locale),
        ]);

        $missions = collect($settings->get('home.missions', []))->map(fn (array $mission) => [
            'icon' => $mission['icon'] ?? 'heart',
            'title' => $this->pick($mission['title'] ?? [], $locale),
            'text' => $this->pick($mission['text'] ?? [], $locale),
        ]);

        $phone = (string) $settings->get('contact.phone');
        $whatsapp = (string) $settings->get('contact.whatsapp');

        return view('livewire.home.home-page', [
            'locale' => $locale,
            'heroTitle' => $settings->text('home.hero_title'),
            'heroTagline' => $settings->text('home.hero_tagline'),
            'features' => $features,
            'story' => [
                'year' => (int) $settings->get('home.story_year'),
                'text' => $settings->text('home.story'),
                'founder' => $settings->text('home.story_founder'),
                'missions' => $missions,
            ],
            'subjects' => Catalog::activeSubjects(),
            'levelsByCycle' => Catalog::levels()->groupBy(fn (Level $level) => $level->cycle->value),
            'cycles' => Cycle::cases(),
            'openingHours' => OpeningHours::summary(),
            'rooms' => Catalog::rooms(),
            'contact' => [
                'address' => $settings->text('contact.address'),
                'phone' => $phone,
                'phone_href' => 'tel:+'.Phone::international($phone),
                'whatsapp_href' => Phone::whatsappUrl($whatsapp, __('home.whatsapp_message')),
                'email' => (string) $settings->get('contact.email'),
                'latitude' => (float) $settings->get('contact.latitude'),
                'longitude' => (float) $settings->get('contact.longitude'),
            ],
        ])->title(__('home.seo_title'));
    }

    /**
     * @param  array<string, ?string>  $translations
     */
    private function pick(array $translations, Locale $locale): string
    {
        return (string) (filled($translations[$locale->value] ?? null) ? $translations[$locale->value] : ($translations[Locale::Fr->value] ?? ''));
    }
}
