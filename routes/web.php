<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeskController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OffboardingRequestController;
use App\Http\Controllers\OnboardingRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified', 'role:admin|hr|it'])->group(function () {
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:admin|hr|it'])->group(function () {
    Route::get('/desks', [DeskController::class, 'index'])->name('desks.index');
    // Static segment must be registered before /desks/{desk} or "create" is treated as a desk id.
    Route::get('/desks/create', [DeskController::class, 'create'])
        ->middleware('role:admin')
        ->name('desks.create');
    Route::get('/desks/{desk}', [DeskController::class, 'show'])->name('desks.show');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('buildings', BuildingController::class)->except(['show']);
        Route::get('buildings/{building}/floors/create', [FloorController::class, 'create'])->name('buildings.floors.create');
        Route::post('buildings/{building}/floors', [FloorController::class, 'store'])->name('buildings.floors.store');
        Route::get('buildings/{building}/floors/{floor}/edit', [FloorController::class, 'edit'])->name('buildings.floors.edit');
        Route::put('buildings/{building}/floors/{floor}', [FloorController::class, 'update'])->name('buildings.floors.update');
        Route::delete('buildings/{building}/floors/{floor}', [FloorController::class, 'destroy'])->name('buildings.floors.destroy');

        Route::resource('departments', DepartmentController::class)->except(['show']);

        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::post('/desks', [DeskController::class, 'store'])->name('desks.store');
    Route::get('/desks/{desk}/edit', [DeskController::class, 'edit'])->name('desks.edit');
    Route::put('/desks/{desk}', [DeskController::class, 'update'])->name('desks.update');
    Route::delete('/desks/{desk}', [DeskController::class, 'destroy'])->name('desks.destroy');
});

Route::middleware(['auth', 'verified', 'role:admin|hr'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');

    Route::get('/onboarding/create', [OnboardingRequestController::class, 'create'])->name('onboarding.create');
    Route::post('/onboarding', [OnboardingRequestController::class, 'store'])->name('onboarding.store');
    Route::post('/onboarding/{onboarding}/assign-desk', [OnboardingRequestController::class, 'assignDesk'])->name('onboarding.assign-desk');
    Route::post('/onboarding/{onboarding}/forward-it', [OnboardingRequestController::class, 'forwardToIt'])->name('onboarding.forward-it');
    Route::post('/onboarding/{onboarding}/complete', [OnboardingRequestController::class, 'complete'])->name('onboarding.complete');
    Route::post('/onboarding/{onboarding}/cancel', [OnboardingRequestController::class, 'cancel'])->name('onboarding.cancel');

    Route::get('/offboarding', [OffboardingRequestController::class, 'index'])->name('offboarding.index');
    Route::get('/offboarding/create', [OffboardingRequestController::class, 'create'])->name('offboarding.create');
    Route::post('/offboarding', [OffboardingRequestController::class, 'store'])->name('offboarding.store');
    Route::get('/offboarding/{offboarding}', [OffboardingRequestController::class, 'show'])->name('offboarding.show');
    Route::post('/offboarding/{offboarding}/complete', [OffboardingRequestController::class, 'complete'])->name('offboarding.complete');
    Route::post('/offboarding/{offboarding}/cancel', [OffboardingRequestController::class, 'cancel'])->name('offboarding.cancel');
});

Route::middleware(['auth', 'verified', 'role:admin|hr|it'])->group(function () {
    Route::get('/onboarding', [OnboardingRequestController::class, 'index'])->name('onboarding.index');
    Route::get('/onboarding/{onboarding}', [OnboardingRequestController::class, 'show'])->name('onboarding.show');
    Route::get('/onboarding/{onboarding}/assign-desk', fn (\App\Models\OnboardingRequest $onboarding) => redirect()->route('onboarding.show', $onboarding));
});

Route::middleware(['auth', 'verified', 'role:admin|hr|it'])->group(function () {
    Route::post('/offboarding/{offboarding}/release-desk', [OffboardingRequestController::class, 'releaseDesk'])->name('offboarding.release-desk');
});

Route::middleware(['auth', 'verified', 'role:it'])->group(function () {
    Route::post('/onboarding/{onboarding}/it/start', [OnboardingRequestController::class, 'itSetupStart'])->name('onboarding.it.start');
    Route::post('/onboarding/{onboarding}/it/complete', [OnboardingRequestController::class, 'itSetupComplete'])->name('onboarding.it.complete');
});

Route::middleware(['auth', 'verified', 'role:admin|it'])->group(function () {
    Route::post('/offboarding/{offboarding}/recovery/start', [OffboardingRequestController::class, 'startRecovery'])->name('offboarding.recovery.start');
    Route::post('/offboarding/{offboarding}/recovery/assets', [OffboardingRequestController::class, 'markAssetsRecovered'])->name('offboarding.recovery.assets');

    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::post('/assets/{asset}/return', [AssetController::class, 'returnActive'])->name('assets.return');
});

Route::middleware('auth')->group(function () {
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
