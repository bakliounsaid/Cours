<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sépare strictement les espaces : role:admin pour /admin, role:teacher pour /enseignant.
 * Un enseignant désactivé est déconnecté.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        abort_unless($user?->role === UserRole::from($role), Response::HTTP_FORBIDDEN, __('ui.unauthorized'));

        if ($user->isTeacher() && ! $user->teacher?->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('teacher.login')->withErrors(['form.email' => __('auth.inactive')]);
        }

        return $next($request);
    }
}
