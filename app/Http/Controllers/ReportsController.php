<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Desk;
use App\Models\Employee;
use App\Models\OffboardingRequest;
use App\Models\OnboardingRequest;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(): View
    {
        $summary = [
            'employees_active' => Employee::query()->where('employment_status', 'active')->count(),
            'employees_total' => Employee::query()->count(),
            'desks_available' => Desk::query()->where('status', 'available')->count(),
            'desks_occupied' => Desk::query()->where('status', 'occupied')->count(),
            'desks_total' => Desk::query()->count(),
            'assets_in_stock' => Asset::query()->where('status', 'in_stock')->count(),
            'assets_assigned' => Asset::query()->where('status', 'assigned')->count(),
            'assets_total' => Asset::query()->count(),
            'onboarding_open' => OnboardingRequest::query()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'offboarding_open' => OffboardingRequest::query()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];

        $onboardingByStatus = $this->countsByStatus(OnboardingRequest::query());
        $offboardingByStatus = $this->countsByStatus(OffboardingRequest::query());
        $employeesByStatus = $this->countsByField(Employee::query(), 'employment_status');
        $desksByStatus = $this->countsByField(Desk::query(), 'status');
        $assetsByStatus = $this->countsByField(Asset::query(), 'status');

        return view('reports.dashboard', compact(
            'summary',
            'onboardingByStatus',
            'offboardingByStatus',
            'employeesByStatus',
            'desksByStatus',
            'assetsByStatus',
        ));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function countsByStatus($query): Collection
    {
        return $this->countsByField($query, 'status');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function countsByField($query, string $field): Collection
    {
        return $query
            ->selectRaw($field.' as label, count(*) as total')
            ->groupBy($field)
            ->orderBy($field)
            ->get()
            ->mapWithKeys(fn ($row) => [$row->label => (int) $row->total]);
    }
}
