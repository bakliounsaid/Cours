<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $wasTeacher = $request->user()?->isTeacher();
        $locale = $request->session()->get('locale');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('locale', $locale);

        return redirect()->route($wasTeacher ? 'teacher.login' : 'login');
    }
}
