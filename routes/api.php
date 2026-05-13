<?php

use App\Http\Controllers\Api\V1\AssetApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeskApiController;
use App\Http\Controllers\Api\V1\EmployeeApiController;
use App\Http\Controllers\Api\V1\OffboardingApiController;
use App\Http\Controllers\Api\V1\OnboardingApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user()->load('roles');
        });

        Route::middleware('role:admin|hr|it')->group(function () {
            Route::get('/employees', [EmployeeApiController::class, 'index']);
            Route::get('/employees/{employee}', [EmployeeApiController::class, 'show']);
            Route::get('/desks', [DeskApiController::class, 'index']);
            Route::get('/desks/{desk}', [DeskApiController::class, 'show']);
            Route::get('/assets', [AssetApiController::class, 'index']);
            Route::get('/assets/{asset}', [AssetApiController::class, 'show']);
            Route::get('/onboarding-requests', [OnboardingApiController::class, 'index']);
            Route::get('/onboarding-requests/{onboarding}', [OnboardingApiController::class, 'show']);
            Route::get('/offboarding-requests', [OffboardingApiController::class, 'index']);
            Route::get('/offboarding-requests/{offboarding}', [OffboardingApiController::class, 'show']);
        });

        Route::middleware('role:admin|hr')->group(function () {
            Route::post('/employees', [EmployeeApiController::class, 'store']);
            Route::patch('/employees/{employee}', [EmployeeApiController::class, 'update']);

            Route::post('/onboarding-requests', [OnboardingApiController::class, 'store']);
            Route::post('/onboarding-requests/{onboarding}/assign-desk', [OnboardingApiController::class, 'assignDesk']);
            Route::post('/onboarding-requests/{onboarding}/forward-it', [OnboardingApiController::class, 'forwardToIt']);
            Route::post('/onboarding-requests/{onboarding}/complete', [OnboardingApiController::class, 'complete']);
            Route::post('/onboarding-requests/{onboarding}/cancel', [OnboardingApiController::class, 'cancel']);

            Route::post('/offboarding-requests', [OffboardingApiController::class, 'store']);
            Route::post('/offboarding-requests/{offboarding}/complete', [OffboardingApiController::class, 'complete']);
            Route::post('/offboarding-requests/{offboarding}/cancel', [OffboardingApiController::class, 'cancel']);
        });

        Route::middleware('role:it')->group(function () {
            Route::post('/onboarding-requests/{onboarding}/it/start', [OnboardingApiController::class, 'itSetupStart']);
            Route::post('/onboarding-requests/{onboarding}/it/complete', [OnboardingApiController::class, 'itSetupComplete']);
        });

        Route::middleware('role:admin|it')->group(function () {
            Route::post('/offboarding-requests/{offboarding}/recovery/start', [OffboardingApiController::class, 'startRecovery']);
            Route::post('/offboarding-requests/{offboarding}/recovery/assets', [OffboardingApiController::class, 'markAssetsRecovered']);

            Route::post('/assets', [AssetApiController::class, 'store']);
            Route::post('/assets/{asset}/assign', [AssetApiController::class, 'assign']);
            Route::post('/assets/{asset}/return', [AssetApiController::class, 'returnActive']);
        });

        Route::middleware('role:admin|hr|it')->group(function () {
            Route::post('/offboarding-requests/{offboarding}/release-desk', [OffboardingApiController::class, 'releaseDesk']);
        });
    });
});
