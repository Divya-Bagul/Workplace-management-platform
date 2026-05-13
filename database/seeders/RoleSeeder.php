<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $hrRole = Role::query()->firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        $itRole = Role::query()->firstOrCreate(['name' => 'it', 'guard_name' => 'web']);

        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@zytix.local'],
            ['name' => 'Platform Admin', 'password' => Hash::make('password')]
        );
        $admin->syncRoles([$adminRole]);

        $hr = User::query()->firstOrCreate(
            ['email' => 'hr@zytix.local'],
            ['name' => 'HR User', 'password' => Hash::make('password')]
        );
        $hr->syncRoles([$hrRole]);

        $it = User::query()->firstOrCreate(
            ['email' => 'it@zytix.local'],
            ['name' => 'IT User', 'password' => Hash::make('password')]
        );
        $it->syncRoles([$itRole]);
    }
}
