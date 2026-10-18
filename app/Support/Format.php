<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Formats d'affichage algériens : dates dans la langue courante,
 * chiffres occidentaux (0-9) dans les deux langues, montants en DA.
 */
class Format
{
    /** « mardi 21 octobre » / « الثلاثاء 21 أكتوبر » */
    public static function dayDate(CarbonInterface|string|null $date, bool $withYear = false): string
    {
        return self::carbon($date)?->translatedFormat($withYear ? 'l j F Y' : 'l j F') ?? '';
    }

    /** « 21 octobre 2026 » */
    public static function longDate(CarbonInterface|string|null $date): string
    {
        return self::carbon($date)?->translatedFormat('j F Y') ?? '';
    }

    /** « 21/10/2026 » */
    public static function date(CarbonInterface|string|null $date): string
    {
        return self::carbon($date)?->format('d/m/Y') ?? '';
    }

    /** « 21/10/2026 17:00 » */
    public static function dateTime(CarbonInterface|string|null $date): string
    {
        return self::carbon($date)?->format('d/m/Y H:i') ?? '';
    }

    /** « 17:00 » */
    public static function time(CarbonInterface|string|null $date): string
    {
        return self::carbon($date)?->format('H:i') ?? '';
    }

    /** « 17:00 – 19:00 » */
    public static function timeRange(CarbonInterface|string|null $start, CarbonInterface|string|null $end): string
    {
        return self::time($start).' – '.self::time($end);
    }

    /** « octobre 2026 » */
    public static function month(CarbonInterface|string|null $date): string
    {
        return self::carbon($date)?->translatedFormat('F Y') ?? '';
    }

    /** Nom du jour à partir de 0 (dimanche) … 6 (samedi). */
    public static function weekday(int $dayOfWeek): string
    {
        return Carbon::now()->startOfWeek(CarbonInterface::SUNDAY)->addDays($dayOfWeek)->translatedFormat('l');
    }

    /** « 2 500 DA » / « 2 500 دج » */
    public static function money(int|float|null $amount): string
    {
        return number_format((float) $amount, 0, ',', ' ').' '.__('app.currency');
    }

    private static function carbon(CarbonInterface|string|null $date): ?CarbonInterface
    {
        if ($date === null || $date === '') {
            return null;
        }

        return $date instanceof CarbonInterface ? $date : Carbon::parse($date);
    }
}
