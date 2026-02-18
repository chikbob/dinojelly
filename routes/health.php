<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| These routes are used for deployment health checks and monitoring.
| They do not require authentication.
|
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/health/detailed', function () {
    $checks = [
        'app' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ];

    // Database check
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $checks['database'] = 'error';
        $checks['database_message'] = $e->getMessage();
    }

    // Cache (Redis) check
    try {
        Cache::store('redis')->put('health_check', true, 10);
        $value = Cache::store('redis')->get('health_check');
        $checks['cache'] = $value ? 'ok' : 'error';
    } catch (\Exception $e) {
        $checks['cache'] = 'error';
        $checks['cache_message'] = $e->getMessage();
    }

    // Storage check
    try {
        $testFile = storage_path('logs/.health_check');
        file_put_contents($testFile, 'test');
        $checks['storage'] = file_exists($testFile) ? 'ok' : 'error';
        @unlink($testFile);
    } catch (\Exception $e) {
        $checks['storage'] = 'error';
        $checks['storage_message'] = $e->getMessage();
    }

    // Determine overall status
    $allOk = collect($checks)
        ->except(['timestamp', 'app'])
        ->every(fn($value) => $value === 'ok');

    $status = $allOk ? 200 : 503;

    return response()->json($checks, $status);
});

Route::get('/health/ready', function () {
    // Check if app is ready to receive traffic
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ready',
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'not_ready',
            'message' => 'Database not available',
            'timestamp' => now()->toIso8601String(),
        ], 503);
    }
});

Route::get('/health/live', function () {
    // Simple liveness probe
    return response()->json([
        'status' => 'alive',
        'timestamp' => now()->toIso8601String(),
    ]);
});
