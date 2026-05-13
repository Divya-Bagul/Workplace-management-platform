<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Desk;
use App\Models\Floor;
use Illuminate\Database\Seeder;

class OfficeLayoutSeeder extends Seeder
{
    public function run(): void
    {
        $building = Building::query()->firstOrCreate(
            ['name' => 'Headquarters'],
            ['address' => '1 Main Street']
        );

        $ground = Floor::query()->firstOrCreate(
            ['building_id' => $building->id, 'name' => 'Ground'],
            ['level' => 0]
        );

        $level1 = Floor::query()->firstOrCreate(
            ['building_id' => $building->id, 'name' => 'Level 1'],
            ['level' => 1]
        );

        foreach (['A-01', 'A-02', 'A-03'] as $code) {
            Desk::query()->firstOrCreate(
                ['floor_id' => $ground->id, 'code' => $code],
                ['status' => 'available']
            );
        }

        foreach (['B-01', 'B-02'] as $code) {
            Desk::query()->firstOrCreate(
                ['floor_id' => $level1->id, 'code' => $code],
                ['status' => 'available']
            );
        }
    }
}
