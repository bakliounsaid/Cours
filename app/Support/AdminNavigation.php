<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Menu de l'espace admin. Seules les routes existantes sont affichées.
 */
class AdminNavigation
{
    /**
     * @return list<array{label: string, items: list<array{route: string, label: string, icon: string, active: string}>}>
     */
    public static function sections(): array
    {
        $sections = [
            'daily' => [
                ['admin.dashboard', 'dashboard', 'home', 'admin.dashboard'],
                ['admin.planning', 'planning', 'calendar-days', 'admin.planning'],
                ['admin.lessons.pending', 'lessons_to_handle', 'warning', 'admin.lessons.*'],
                ['admin.enrollments', 'enrollments', 'inbox', 'admin.enrollments*'],
            ],
            'management' => [
                ['admin.students.index', 'students', 'users', 'admin.students.*'],
                ['admin.teachers', 'teachers', 'academic-cap', 'admin.teachers*'],
                ['admin.groups', 'groups', 'user-group', 'admin.groups'],
            ],
            'finance' => [
                ['admin.payments', 'payments', 'banknotes', 'admin.payments'],
                ['admin.unpaid', 'unpaid', 'megaphone', 'admin.unpaid'],
                ['admin.payroll', 'payroll', 'calculator', 'admin.payroll'],
            ],
            'school' => [
                ['admin.rooms', 'rooms', 'building', 'admin.rooms'],
                ['admin.subjects', 'subjects', 'book-open', 'admin.subjects'],
                ['admin.levels', 'levels', 'layers', 'admin.levels'],
                ['admin.time-slots', 'time_slots', 'clock', 'admin.time-slots'],
                ['admin.settings', 'settings', 'cog', 'admin.settings'],
            ],
        ];

        $result = [];

        foreach ($sections as $section => $items) {
            $items = array_values(array_filter(array_map(
                fn (array $item) => Route::has($item[0]) ? [
                    'route' => $item[0],
                    'label' => __('admin.nav.'.$item[1]),
                    'icon' => $item[2],
                    'active' => $item[3],
                ] : null,
                $items
            )));

            if ($items !== []) {
                $result[] = ['label' => __('admin.nav.sections.'.$section), 'items' => $items];
            }
        }

        return $result;
    }
}
