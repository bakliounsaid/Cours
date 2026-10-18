<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Détermine la langue de la requête :
 * paramètre d'URL (/fr, /ar) > session > préférence du compte > langue par défaut.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $fromUrl = $request->route('locale');

        $locale = ($fromUrl instanceof Locale ? $fromUrl : Locale::tryFrom((string) $fromUrl))
            ?? Locale::tryFrom((string) $request->session()->get('locale'))
            ?? $request->user()?->locale
            ?? Locale::default();

        $request->session()->put('locale', $locale->value);
        app()->setLocale($locale->value);

        return $next($request);
    }
}
