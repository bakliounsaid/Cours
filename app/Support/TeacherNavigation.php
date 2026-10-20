<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Menu latéral de l'espace enseignant. Seules les routes existantes sont affichées.
 */
class TeacherNavigation
{
    private const SECTIONS = [
        // [route, libellé, icône, motif de route active]
        'daily' => [
            ['teacher.today', 'dashboard', 'home', 'teacher.today'],
            ['teacher.lessons', 'lessons', 'clipboard-check', 'teacher.lessons*'],
            ['teacher.schedule', 'schedule', 'calendar-days', 'teacher.schedule'],
        ],
        'management' => [
            ['teacher.groups', 'groups', 'user-group', 'teacher.groups*'],
            ['teacher.students', 'students', 'users', 'teacher.students*'],
        ],
        'me' => [
            ['teacher.absence', 'absence', 'hand-raised', 'teacher.absence'],
            ['teacher.hours', 'hours', 'clock', 'teacher.hours'],
        ],
    ];

    /**
     * @return list<array{label: string, items: list<array{url: string, label: string, icon: string, active: bool}>}>
     */
    public static function sections(): array
    {
        $result = [];

        foreach (self::SECTIONS as $section => $items) {
            $links = [];

            foreach ($items as [$route, $label, $icon, $pattern]) {
                if (Route::has($route)) {
                    $links[] = [
                        'url' => route($route),
                        'label' => __('teacher.nav.'.$label),
                        'icon' => $icon,
                        'active' => request()->routeIs($pattern),
                    ];
                }
            }

            if ($links !== []) {
                $result[] = ['label' => __('teacher.nav.sections.'.$section), 'items' => $links];
            }
        }

        return $result;
    }
}
