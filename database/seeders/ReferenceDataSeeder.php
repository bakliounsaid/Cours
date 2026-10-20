<?php

namespace Database\Seeders;

use App\Enums\Cycle;
use App\Models\Level;
use App\Models\Room;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Settings\DefaultSettings;
use App\Settings\Settings;
use Illuminate\Database\Seeder;

/**
 * Données de référence de l'école (à exécuter aussi en production).
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(Settings $settings): void
    {
        $this->rooms();
        $this->subjects();
        $this->levels();
        $this->timeSlots();

        // Lu en base, pas dans le cache : après un migrate:fresh, le cache peut encore
        // contenir les anciens réglages et aucun ne serait recréé.
        $existing = Setting::query()->pluck('key')->flip()->all();
        $settings->setMany(array_diff_key(DefaultSettings::all(), $existing));
    }

    private function rooms(): void
    {
        foreach ([['Portail', '#D9774B', 1], ['Mur', '#2F5D50', 2]] as [$name, $color, $order]) {
            Room::query()->updateOrCreate(['name' => $name], ['capacity' => 15, 'color' => $color, 'sort_order' => $order]);
        }
    }

    private function subjects(): void
    {
        $subjects = [
            ['maths', 'Mathématiques', 'الرياضيات', 'Mathematics', '#B9572F', 'calculator'],
            ['physique', 'Physique', 'الفيزياء', 'Physics', '#2F5D50', 'light-bulb'],
            ['arabe', 'Arabe', 'اللغة العربية', 'Arabic', '#557F60', 'book-open'],
            ['francais', 'Français', 'اللغة الفرنسية', 'French', '#8A6512', 'chat'],
            ['anglais', 'Anglais', 'اللغة الإنجليزية', 'English', '#3E6B78', 'sparkles'],
        ];

        foreach ($subjects as $order => [$slug, $fr, $ar, $en, $color, $icon]) {
            Subject::query()->updateOrCreate(['slug' => $slug], [
                'name' => ['fr' => $fr, 'ar' => $ar, 'en' => $en],
                'color' => $color,
                'icon' => $icon,
                'sort_order' => $order + 1,
            ]);
        }
    }

    private function levels(): void
    {
        $ordinalsFr = [1 => '1re', 2 => '2e', 3 => '3e', 4 => '4e', 5 => '5e'];
        $ordinalsAr = [1 => 'الأولى', 2 => 'الثانية', 3 => 'الثالثة', 4 => 'الرابعة', 5 => 'الخامسة'];
        $ordinalsEn = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th', 5 => '5th'];

        $order = 0;

        foreach (Cycle::cases() as $cycle) {
            foreach (range(1, $cycle->years()) as $year) {
                [$fr, $ar, $en] = $cycle === Cycle::Primary
                    ? ["{$ordinalsFr[$year]} année primaire", "السنة {$ordinalsAr[$year]} ابتدائي", "{$ordinalsEn[$year]} year primary"]
                    : ["{$ordinalsFr[$year]} année moyenne (CEM)", "السنة {$ordinalsAr[$year]} متوسط", "{$ordinalsEn[$year]} year middle school (CEM)"];

                Level::query()->updateOrCreate(
                    ['cycle' => $cycle, 'year' => $year],
                    ['name' => ['fr' => $fr, 'ar' => $ar, 'en' => $en], 'sort_order' => ++$order],
                );
            }
        }
    }

    /**
     * Grille horaire : séances de 2 h.
     * Dimanche → jeudi : 17:00–19:00
     * Vendredi : 08:00–12:00 et 13:30–17:30
     * Samedi : 08:00–12:00, pause de 30 min, 12:30–18:30
     */
    private function timeSlots(): void
    {
        $slots = [];

        foreach (range(0, 4) as $day) {
            $slots[] = [$day, '17:00', '19:00'];
        }

        foreach ([['08:00', '10:00'], ['10:00', '12:00'], ['13:30', '15:30'], ['15:30', '17:30']] as [$start, $end]) {
            $slots[] = [5, $start, $end];
        }

        foreach ([['08:00', '10:00'], ['10:00', '12:00'], ['12:30', '14:30'], ['14:30', '16:30'], ['16:30', '18:30']] as [$start, $end]) {
            $slots[] = [6, $start, $end];
        }

        foreach ($slots as [$day, $start, $end]) {
            TimeSlot::query()->updateOrCreate(
                ['day_of_week' => $day, 'starts_at' => $start.':00'],
                ['ends_at' => $end.':00', 'is_active' => true],
            );
        }
    }
}
