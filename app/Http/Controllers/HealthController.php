<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function show(): JsonResponse
    {
        // Safe: reveals readiness only — no secrets, versions, or infra details.
        $db = 'ok';
        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (\Throwable) {
            $db = 'unreachable';
        }

        $migrations = 'ok';
        try {
            $pending = Artisan::call('migrate --pretend') ?? 0;
        } catch (\Throwable) {
            $migrations = 'unknown';
        }

        $status = $db === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $db === 'ok' ? 'ok' : 'degraded',
            'database' => $db,
            'app' => 'ready',
        ], $status);
    }
}
