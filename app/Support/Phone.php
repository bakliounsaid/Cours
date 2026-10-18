<?php

namespace App\Support;

/**
 * Numéros de téléphone algériens.
 * Stockés sous forme nationale « 0555123456 », affichés « 0555 12 34 56 »,
 * convertis en « 213555123456 » pour WhatsApp et les SMS.
 */
class Phone
{
    /**
     * Forme nationale (10 chiffres) ou null si le numéro n'est pas algérien valide.
     * Accepte : 0555 12 34 56, 0555-12-34-56, +213 555 12 34 56, 00213555123456, 021 23 45 67…
     */
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '00213')) {
            $digits = '0'.substr($digits, 5);
        } elseif (str_starts_with($digits, '213')) {
            $digits = '0'.substr($digits, 3);
        }

        // Mobiles : 05, 06, 07 + 8 chiffres ; fixes : 02, 03, 04 + 7 chiffres
        if (preg_match('/^0[567]\d{8}$/', $digits) || preg_match('/^0[234]\d{7}$/', $digits)) {
            return $digits;
        }

        return null;
    }

    public static function isValid(?string $phone): bool
    {
        return self::normalize($phone) !== null;
    }

    /** « 0555 12 34 56 » */
    public static function format(?string $phone): string
    {
        $national = self::normalize($phone);

        if ($national === null) {
            return (string) $phone;
        }

        if (strlen($national) === 10) {
            return substr($national, 0, 4).' '.implode(' ', str_split(substr($national, 4), 2));
        }

        return substr($national, 0, 3).' '.implode(' ', str_split(substr($national, 3), 2));
    }

    /** « 213555123456 » (format international sans « + ») */
    public static function international(?string $phone): string
    {
        $national = self::normalize($phone);

        return $national === null ? preg_replace('/\D/', '', (string) $phone) : '213'.substr($national, 1);
    }

    public static function whatsappUrl(?string $phone, ?string $text = null): string
    {
        return 'https://wa.me/'.self::international($phone).($text ? '?text='.rawurlencode($text) : '');
    }
}
