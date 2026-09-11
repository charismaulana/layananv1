<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/**
 * =========================================================
 *  AUTO-DETECT: LOKAL vs SHARED HOSTING
 * =========================================================
 *
 * File ini otomatis mendeteksi environment:
 *
 * [LOKAL / Development]
 *   project/
 *   ├── public/
 *   │   └── index.php  ← file ini
 *   ├── vendor/        ← vendor ada satu level di atas public/
 *   └── bootstrap/
 *
 * [HOSTING / Production - Hostinger]
 *   public_html/
 *   ├── index.php      ← file ini (disalin dari ramba-meal/public/)
 *   ├── build/
 *   └── ramba-meal/    ← project Laravel (git clone)
 *       ├── vendor/
 *       └── bootstrap/
 * =========================================================
 */

// Auto-detect: cek apakah vendor ada di path lokal (satu level atas)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // ✅ Environment LOKAL: jalankan dengan php artisan serve
    $laravelPath = __DIR__ . '/..';
} else {
    // ✅ Environment HOSTING: file ini ada di public_html/
    $laravelPath = __DIR__ . '/ramba-meal';
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $laravelPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $laravelPath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $laravelPath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());

