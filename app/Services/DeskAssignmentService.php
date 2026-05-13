<?php

namespace App\Services;

use App\Models\Desk;
use App\Models\DeskAllocation;
use App\Models\Employee;
use Carbon\CarbonInterface;

class DeskAssignmentService
{
    public function endOpenAllocationsForEmployee(Employee $employee, CarbonInterface $until): void
    {
        $deskIds = DeskAllocation::query()
            ->where('employee_id', $employee->id)
            ->whereNull('valid_to')
            ->pluck('desk_id')
            ->unique();

        DeskAllocation::query()
            ->where('employee_id', $employee->id)
            ->whereNull('valid_to')
            ->update(['valid_to' => $until->toDateString()]);

        foreach ($deskIds as $deskId) {
            $desk = Desk::query()->find($deskId);
            if ($desk) {
                $this->syncDeskStatusFromAllocations($desk);
            }
        }
    }

    public function assignDeskToEmployee(
        Employee $employee,
        Desk $desk,
        CarbonInterface $validFrom,
        ?string $notes = null,
    ): DeskAllocation {
        $this->endOpenAllocationsForEmployee($employee, $validFrom->copy()->subDay());

        $allocation = DeskAllocation::query()->create([
            'desk_id' => $desk->id,
            'employee_id' => $employee->id,
            'valid_from' => $validFrom->toDateString(),
            'valid_to' => null,
            'notes' => $notes,
        ]);

        if (in_array($desk->status, ['available', 'reserved'], true)) {
            $desk->update(['status' => 'occupied']);
        }

        return $allocation;
    }

    public function releaseDeskForEmployee(Employee $employee, CarbonInterface $releasedOn): void
    {
        $this->endOpenAllocationsForEmployee($employee, $releasedOn);
    }

    public function syncDeskStatusFromAllocations(Desk $desk): void
    {
        $hasActive = DeskAllocation::query()
            ->where('desk_id', $desk->id)
            ->whereNull('valid_to')
            ->exists();

        if (! $hasActive && $desk->status === 'occupied') {
            $desk->update(['status' => 'available']);
        }
    }
}
