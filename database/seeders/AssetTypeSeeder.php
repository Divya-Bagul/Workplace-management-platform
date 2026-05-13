<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Laptop', 'slug' => 'laptop'],
            ['name' => 'Mouse', 'slug' => 'mouse'],
            ['name' => 'Monitor', 'slug' => 'monitor'],
            ['name' => 'CPU', 'slug' => 'cpu'],
            ['name' => 'Headphones', 'slug' => 'headphones'],
        ];

        foreach ($types as $type) {
            AssetType::query()->firstOrCreate(['slug' => $type['slug']], $type);
        }
    }
}
