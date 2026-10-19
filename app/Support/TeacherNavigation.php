<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Onglets de l'espace enseignant (barre du bas). Seules les routes existantes sont affichées.
 */
class TeacherNavigation
{
    private const TABS = [
        // [route, libellé, icône, motif de route active]
        ['teacher.today', 'today', 'sun', 'teacher.today'],
        ['teacher.groups', 'groups', 'user-group', 'teacher.groups*'],
        ['teacher.students', 'students', 'users', 'teacher.students*'],
        ['teacher.week', 'week', 'calendar', 'teacher.week'],
        ['teacher.absence', 'absence', 'hand-raised', 'teacher.absence'],
        ['teacher.hours', 'hours', 'clock', 'teacher.hours'],
    ];

    /**
     * @return list<array{url: string, label: string, icon: string, active: bool}>
     */
    public static function tabs(): array
    {
        $tabs = [];

        foreach (self::TABS as [$route, $label, $icon, $pattern]) {
            if (Route::has($route)) {
                $tabs[] = [
                    'url' => route($route),
                    'label' => __('teacher.nav.'.$label),
                    'icon' => $icon,
                    'active' => request()->routeIs($pattern),
                ];
            }
        }

        return $tabs;
    }

    /** Position de l'onglet actif (pour le repère animé), ou null. */
    public static function activeIndex(array $tabs): ?int
    {
        $index = array_search(true, array_column($tabs, 'active'), true);

        return $index === false ? null : $index;
    }
}
