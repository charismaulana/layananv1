<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Phase 2 (Mobile App)
|--------------------------------------------------------------------------
| API endpoints will be fully implemented in Phase 2 with Sanctum auth.
| Currently returns 501 Not Implemented for all routes.
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::fallback(function () {
    return response()->json(['message' => 'API tidak tersedia di Phase 1. Gunakan web interface.'], 501);
});
