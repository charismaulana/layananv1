<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

// ── Guest routes ──────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
});

// ── Authenticated routes ───────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Password change (before password.changed middleware blocks)
    Route::get('/ganti-password',  [Controllers\Auth\PasswordChangeController::class, 'show'])->name('password.change');
    Route::post('/ganti-password', [Controllers\Auth\PasswordChangeController::class, 'update'])->name('password.change.update');
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Portal Layanan Terpadu General Services (GS)
    Route::get('/portal', [Controllers\PortalController::class, 'index'])->name('portal');

    // Dashboard (branches per role default)
    Route::get('/', [Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/beranda', [Controllers\DashboardController::class, 'beranda'])->name('beranda');

    // Profile
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // ── Roster ────────────────────────────────────────────────────────────────
    Route::prefix('roster')->name('roster.')->group(function () {
        Route::get('/',         [Controllers\RosterController::class, 'index'])->name('index');
        Route::post('/',        [Controllers\RosterController::class, 'store'])->name('store');
        Route::post('/bulk',    [Controllers\RosterController::class, 'storeBulk'])->name('bulk');
        Route::post('/salin',   [Controllers\RosterController::class, 'copyRoster'])->name('copy');
    });

    // ── Meal Plan ─────────────────────────────────────────────────────────────
    Route::prefix('rencana-makan')->name('meal-plan.')->group(function () {
        Route::get('/',                    [Controllers\MealPlanController::class, 'index'])->name('index');
        Route::patch('/{mealPlan}/lokasi', [Controllers\MealPlanController::class, 'updateLocation'])->name('update-location');
    });

    // ── Movement ──────────────────────────────────────────────────────────────
    Route::prefix('movement')->name('movement.')->group(function () {
        Route::get('/',          [Controllers\MovementController::class, 'index'])->name('index');
        Route::get('/buat',      [Controllers\MovementController::class, 'create'])->name('create');
        Route::post('/',         [Controllers\MovementController::class, 'store'])->name('store');
        Route::get('/{movement}', [Controllers\MovementController::class, 'show'])->name('show');
        Route::post('/{movement}/setujui', [Controllers\MovementController::class, 'approve'])->name('approve');
        Route::post('/{movement}/tolak',   [Controllers\MovementController::class, 'reject'])->name('reject');
    });

    // ── Outside Meal ──────────────────────────────────────────────────────────
    Route::prefix('outside-meal')->name('outside-meal.')->group(function () {
        Route::get('/',           [Controllers\OutsideMealController::class, 'index'])->name('index');
        Route::get('/buat',       [Controllers\OutsideMealController::class, 'create'])->name('create');
        Route::post('/',          [Controllers\OutsideMealController::class, 'store'])->name('store');
        Route::get('/{outsideMeal}',   [Controllers\OutsideMealController::class, 'show'])->name('show');
        Route::post('/{outsideMeal}/setujui', [Controllers\OutsideMealController::class, 'approve'])->name('approve');
        Route::post('/{outsideMeal}/tolak',   [Controllers\OutsideMealController::class, 'reject'])->name('reject');
    });

    // ── Cancellation ──────────────────────────────────────────────────────────
    Route::prefix('batalkan-makan')->name('cancellation.')->group(function () {
        Route::get('/{mealPlan}',                   [Controllers\CancellationController::class, 'create'])->name('create');
        Route::post('/{mealPlan}',                  [Controllers\CancellationController::class, 'store'])->name('store');
        Route::post('/{mealPlan}/aktifkan-kembali', [Controllers\CancellationController::class, 'reactivate'])->name('reactivate');
    });

    // ── Menu ─────────────────────────────────────────────────────────────────
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/',      [Controllers\MenuController::class, 'index'])->name('index');
        Route::get('/buat',  [Controllers\MenuController::class, 'create'])->name('create');
        Route::post('/',     [Controllers\MenuController::class, 'store'])->name('store');
    });

    // ── Rating & Saran ────────────────────────────────────────────────────────
    Route::get('/rating/buat',    [Controllers\RatingController::class, 'create'])->name('rating.create');
    Route::post('/rating',        [Controllers\RatingController::class, 'store'])->name('rating.store');
    Route::get('/saran/buat',     [Controllers\SuggestionController::class, 'create'])->name('suggestion.create');
    Route::post('/saran',         [Controllers\SuggestionController::class, 'store'])->name('suggestion.store');

    // ── Meal Card ─────────────────────────────────────────────────────────────
    Route::get('/kartu-makan',         [Controllers\MealCardController::class, 'show'])->name('meal-card.show');
    Route::post('/kartu-makan/refresh',[Controllers\MealCardController::class, 'regenerate'])->name('meal-card.regenerate');

    // ── Manifest ──────────────────────────────────────────────────────────────
    Route::prefix('manifest')->name('manifest.')->group(function () {
        Route::get('/',                           [Controllers\ManifestController::class, 'index'])->name('index');
        Route::post('/generate',                  [Controllers\ManifestController::class, 'generate'])->name('generate');
        Route::get('/messhall/{date}/{key}',      [Controllers\ManifestController::class, 'showMessHall'])->name('messhall.show');
        Route::get('/messhall/{date}/{key}/pdf',  [Controllers\ManifestController::class, 'pdfMessHall'])->name('messhall.pdf');
        Route::get('/pengantaran/{date}',         [Controllers\ManifestController::class, 'showDelivery'])->name('delivery.show');
        Route::get('/pengantaran/{date}/pdf',     [Controllers\ManifestController::class, 'pdfDelivery'])->name('delivery.pdf');
        Route::post('/setting/vendor',            [Controllers\ManifestController::class, 'updateCateringVendor'])->name('setting.vendor');
        Route::get('/{manifest}',                 [Controllers\ManifestController::class, 'show'])->name('show');
        Route::get('/{manifest}/pdf',             [Controllers\ManifestController::class, 'pdf'])->name('pdf');
    });

    // ── Visitor Meals (Khusus Role GS & SysAdmin) ───────────────────────────
    Route::middleware('role:gs,system-admin')->prefix('visitor-meals')->name('visitor-meals.')->group(function () {
        Route::post('/',                   [Controllers\VisitorMealController::class, 'store'])->name('store');
        Route::put('/{visitorMeal}',       [Controllers\VisitorMealController::class, 'update'])->name('update');
        Route::delete('/{visitorMeal}',    [Controllers\VisitorMealController::class, 'destroy'])->name('destroy');
    });

    // ── Summary Rating, Saran & Masukan (Khusus Role GS, Catering, SysAdmin) ─
    Route::middleware('role:gs,catering,system-admin')->prefix('ulasan')->name('feedback.')->group(function () {
        Route::get('/',                                  [Controllers\FeedbackController::class, 'index'])->name('index');
        Route::get('/export',                            [Controllers\FeedbackController::class, 'export'])->name('export');
        Route::patch('/saran/{suggestion}/toggle-read',  [Controllers\FeedbackController::class, 'toggleRead'])->name('toggle-read');
    });

    // ── Ekspor Data Excel (Khusus Role GS, Catering, SysAdmin) ─────────────
    Route::middleware('role:gs,catering,system-admin')->prefix('admin/export')->name('admin.export.')->group(function () {
        Route::get('/', [Controllers\Admin\ExportController::class, 'index'])->name('index');
        Route::get('/{type}', [Controllers\Admin\ExportController::class, 'download'])->name('download');
    });

    // ── GS Admin ──────────────────────────────────────────────────────────────
    Route::middleware('role:gs,system-admin')->prefix('admin')->name('admin.')->group(function () {

        // Users
        Route::resource('users', Controllers\Admin\UserController::class)->except(['destroy']);
        Route::post('/users/{user}/setujui-kontraktor', [Controllers\Admin\UserController::class, 'approveContractor'])->name('users.approve-contractor');
        Route::post('/users/{user}/tolak-kontraktor',   [Controllers\Admin\UserController::class, 'rejectContractor'])->name('users.reject-contractor');

        // Master Data (Wilayah, Departemen, Lokasi Makan)
        Route::prefix('master')->name('master.')->group(function () {
            Route::get('/',                          [Controllers\Admin\MasterDataController::class, 'index'])->name('index');
            Route::post('/regions',                  [Controllers\Admin\MasterDataController::class, 'storeRegion'])->name('regions.store');
            Route::put('/regions/{region}',          [Controllers\Admin\MasterDataController::class, 'updateRegion'])->name('regions.update');
            Route::patch('/regions/{region}/toggle', [Controllers\Admin\MasterDataController::class, 'toggleRegion'])->name('regions.toggle');

            Route::post('/departments',                      [Controllers\Admin\MasterDataController::class, 'storeDepartment'])->name('departments.store');
            Route::put('/departments/{department}',          [Controllers\Admin\MasterDataController::class, 'updateDepartment'])->name('departments.update');
            Route::patch('/departments/{department}/toggle', [Controllers\Admin\MasterDataController::class, 'toggleDepartment'])->name('departments.toggle');

            Route::post('/meal-locations',                          [Controllers\Admin\MasterDataController::class, 'storeMealLocation'])->name('meal-locations.store');
            Route::put('/meal-locations/{mealLocation}',            [Controllers\Admin\MasterDataController::class, 'updateMealLocation'])->name('meal-locations.update');
            Route::patch('/meal-locations/{mealLocation}/toggle',   [Controllers\Admin\MasterDataController::class, 'toggleMealLocation'])->name('meal-locations.toggle');

            Route::post('/rooms',                 [Controllers\Admin\MasterDataController::class, 'storeRoom'])->name('rooms.store');
            Route::put('/rooms/{room}',           [Controllers\Admin\MasterDataController::class, 'updateRoom'])->name('rooms.update');
            Route::patch('/rooms/{room}/toggle',  [Controllers\Admin\MasterDataController::class, 'toggleRoom'])->name('rooms.toggle');
        });

        // Dashboard Mess (Pemantauan Penghuni & Status Masuk)
        Route::get('/mess', [Controllers\Admin\MessController::class, 'index'])->name('mess.index');

        // Activity Log
        Route::get('/aktivitas', [Controllers\Admin\ActivityController::class, 'index'])->name('activity');
    });
});
