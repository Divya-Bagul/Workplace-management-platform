<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Floor;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $engineering = Department::query()->where('code', 'ENG')->first();
        $building = Building::query()->where('name', 'Headquarters')->first();
        $floor = $building
            ? Floor::query()
                ->where('building_id', $building->id)
                ->where('name', 'Ground')
                ->first()
            : null;

        $manager = Employee::query()->firstOrCreate(
            ['employee_code' => 'EMP-MGR-001'],
            [
                'name' => 'Priya Sharma',
                'email' => 'priya.sharma@zytix.local',
                'department_id' => $engineering?->id,
                'designation' => 'Engineering Manager',
                'joining_date' => '2020-01-15',
                'building_id' => $building?->id,
                'floor_id' => $floor?->id,
                'employment_status' => 'active',
            ]
        );

        $reports = [
            [
                'employee_code' => 'EMP-REP-001',
                'name' => 'Divya Bagul',
                'email' => 'divya.bagul@zytix.local',
                'designation' => 'Software Engineer',
                'joining_date' => '2025-06-01',
            ],
            [
                'employee_code' => 'EMP-REP-002',
                'name' => 'Arjun Mehta',
                'email' => 'arjun.mehta@zytix.local',
                'designation' => 'QA Engineer',
                'joining_date' => '2025-07-01',
            ],
        ];

        foreach ($reports as $report) {
            Employee::query()->updateOrCreate(
                ['employee_code' => $report['employee_code']],
                [
                    'name' => $report['name'],
                    'email' => $report['email'],
                    'department_id' => $engineering?->id,
                    'designation' => $report['designation'],
                    'joining_date' => $report['joining_date'],
                    'reporting_manager_id' => $manager->id,
                    'building_id' => $building?->id,
                    'floor_id' => $floor?->id,
                    'employment_status' => 'active',
                ]
            );
        }
    }
}
