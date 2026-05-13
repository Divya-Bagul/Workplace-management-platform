<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Information Technology', 'code' => 'IT'],
            ['name' => 'Operations', 'code' => 'OPS'],
        ];

        foreach ($rows as $row) {
            Department::query()->firstOrCreate(['code' => $row['code']], $row);
        }
    }
}
