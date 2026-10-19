<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Détermine la langue de la requête :
 * paramètre d'URL (/fr, /ar, /en) > session > langue par défaut (français).
 * La langue n'est jamais enregistrée en base : uniquement dans la session.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $fromUrl = $request->route('locale');

        $locale = ($fromUrl instanceof Locale ? $fromUrl : Locale::tryFrom((string) $fromUrl))
            ?? Locale::tryFrom((string) $request->session()->get('locale'))
            ?? Locale::default();

        $request->session()->put('locale', $locale->value);
        app()->setLocale($locale->value);

        return $next($request);
    }
}
