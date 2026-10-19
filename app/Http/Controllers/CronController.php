<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Tâches planifiées déclenchées par Vercel Cron (pas de « schedule:work » en serverless).
 * Vercel envoie « Authorization: Bearer <CRON_SECRET> ».
 */
class CronController extends Controller
{
    public function lessons(Request $request): JsonResponse
    {
        $secret = (string) config('educoeur.cron_secret');

        abort_if($secret === '' || ! hash_equals('Bearer '.$secret, (string) $request->header('Authorization')), 403);

        Artisan::call('lessons:generate');

        return response()->json(['ok' => true, 'output' => trim(Artisan::output())]);
    }
}
