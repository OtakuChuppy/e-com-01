<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    public function show(): JsonResponse
    {
        // Safe: reveals readiness only — no secrets, versions, or infra details.
        // Cheap by design: single SELECT, no migrations scan, no artisan calls.
        $db = 'ok';
        try {
            DB::select('select 1');
        } catch (\Throwable) {
            $db = 'unreachable';
        }

        $status = $db === 'ok' ? 200 : 503;

        return response()->json([
            'status' => $db === 'ok' ? 'ok' : 'degraded',
            'database' => $db,
            'app' => 'ready',
        ], $status);
    }
}
