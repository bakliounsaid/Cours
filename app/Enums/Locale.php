<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum Locale: string
{
    use HasLabel;

    case Fr = 'fr';
    case Ar = 'ar';
    case En = 'en';

    public static function default(): self
    {
        return self::from(config('app.locale'));
    }

    public static function current(): self
    {
        return self::tryFrom(app()->getLocale()) ?? self::default();
    }

    public function direction(): string
    {
        return config("educoeur.locales.{$this->value}.dir");
    }

    public function isRtl(): bool
    {
        return $this->direction() === 'rtl';
    }

    public function carbonLocale(): string
    {
        return config("educoeur.locales.{$this->value}.carbon");
    }

    public function nativeName(): string
    {
        return config("educoeur.locales.{$this->value}.native");
    }

    public function shortName(): string
    {
        return config("educoeur.locales.{$this->value}.short");
    }

    /** Locale Open Graph (« fr_DZ », « ar_DZ », « en_US »). */
    public function openGraph(): string
    {
        return config("educoeur.locales.{$this->value}.og");
    }
}
