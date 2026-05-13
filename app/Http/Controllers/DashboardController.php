<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Desk;
use App\Models\Employee;
use App\Models\OffboardingRequest;
use App\Models\OnboardingRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'employees' => 0,
            'desks_available' => 0,
            'desks_occupied' => 0,
            'assets_in_stock' => 0,
            'onboarding_open' => 0,
            'offboarding_open' => 0,
        ];

        if (auth()->user()->hasAnyRole(['admin', 'hr', 'it'])) {
            $stats = [
                'employees' => Employee::query()->where('employment_status', 'active')->count(),
                'desks_available' => Desk::query()->where('status', 'available')->count(),
                'desks_occupied' => Desk::query()->where('status', 'occupied')->count(),
                'assets_in_stock' => Asset::query()->where('status', 'in_stock')->count(),
                'onboarding_open' => OnboardingRequest::query()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->count(),
                'offboarding_open' => OffboardingRequest::query()
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->count(),
            ];
        }

        return view('dashboard', compact('stats'));
    }
}
