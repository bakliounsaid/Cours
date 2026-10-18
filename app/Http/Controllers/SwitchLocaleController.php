<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SwitchLocaleController extends Controller
{
    public function __invoke(Request $request, Locale $locale): RedirectResponse
    {
        $request->session()->put('locale', $locale->value);

        $request->user()?->update(['locale' => $locale]);

        $previous = url()->previous();

        // Sur la page d'accueil, on bascule vers l'URL de la langue (/fr ↔ /ar).
        if (in_array(parse_url($previous, PHP_URL_PATH) ?? '/', ['/', '/fr', '/ar'], true)) {
            return redirect()->route('home.localized', ['locale' => $locale->value]);
        }

        return redirect()->to($previous);
    }
}
