<?php

namespace App\Support;

use App\Models\TimeSlot;
use Illuminate\Support\Collection;

/**
 * Horaires d'ouverture calculés à partir des créneaux actifs :
 * créneaux contigus fusionnés, courte pause (≤ 30 min) signalée, jours identiques regroupés.
 *
 * Exemple : « Dimanche – Jeudi : 17:00 – 19:00 », « Samedi : 08:00 – 18:30 (pause de 30 min) ».
 */
class OpeningHours
{
    private const SHORT_BREAK_MINUTES = 30;

    /** Ordre de la semaine algérienne : dimanche → samedi. */
    private const WEEK = [0, 1, 2, 3, 4, 5, 6];

    /**
     * @return list<array{days: string, ranges: list<string>, short_break: bool}>
     */
    public static function summary(): array
    {
        $slotsByDay = Catalog::activeTimeSlots()->groupBy('day_of_week');

        $days = [];

        foreach (self::WEEK as $day) {
            if (! $slotsByDay->has($day)) {
                continue;
            }

            $days[$day] = self::rangesFor($slotsByDay[$day]);
        }

        return self::groupIdenticalDays($days);
    }

    /**
     * @param  Collection<int, TimeSlot>  $slots
     * @return array{ranges: list<string>, short_break: bool}
     */
    private static function rangesFor(Collection $slots): array
    {
        $ranges = [];
        $shortBreak = false;

        foreach ($slots as $slot) {
            $start = $slot->startTime();
            $end = $slot->endTime();
            $last = array_key_last($ranges);

            if ($last !== null) {
                $gap = self::minutes($start) - self::minutes($ranges[$last][1]);

                if ($gap <= self::SHORT_BREAK_MINUTES) {
                    $shortBreak = $shortBreak || $gap > 0;
                    $ranges[$last][1] = $end;

                    continue;
                }
            }

            $ranges[] = [$start, $end];
        }

        return [
            'ranges' => array_map(fn (array $range) => $range[0].' – '.$range[1], $ranges),
            'short_break' => $shortBreak,
        ];
    }

    /**
     * @param  array<int, array{ranges: list<string>, short_break: bool}>  $days
     * @return list<array{days: string, ranges: list<string>, short_break: bool}>
     */
    private static function groupIdenticalDays(array $days): array
    {
        $groups = [];

        foreach ($days as $day => $hours) {
            $last = array_key_last($groups);

            if ($last !== null && $groups[$last]['hours'] === $hours && $groups[$last]['to'] === $day - 1) {
                $groups[$last]['to'] = $day;

                continue;
            }

            $groups[] = ['from' => $day, 'to' => $day, 'hours' => $hours];
        }

        return array_map(fn (array $group) => [
            'days' => $group['from'] === $group['to']
                ? mb_convert_case(Format::weekday($group['from']), MB_CASE_TITLE)
                : mb_convert_case(Format::weekday($group['from']), MB_CASE_TITLE).' – '.mb_convert_case(Format::weekday($group['to']), MB_CASE_TITLE),
            'ranges' => $group['hours']['ranges'],
            'short_break' => $group['hours']['short_break'],
        ], $groups);
    }

    private static function minutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }
}
