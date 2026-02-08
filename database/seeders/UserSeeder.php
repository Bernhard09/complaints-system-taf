<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $finance = Department::where('name', 'FINANCE')->first();

        User::firstOrCreate(
            ['email' => 'user@test.com'],
            ['name' => 'Test User', 'password' => Hash::make('password'), 'role' => 'USER']
        );

        User::firstOrCreate(
            ['email' => 'supervisor@test.com'],
            ['name' => 'Supervisor', 'password' => Hash::make('password'), 'role' => 'SUPERVISOR']
        );

        User::firstOrCreate(
            ['email' => 'agent@test.com'],
            [
                'name' => 'Agent Finance',
                'password' => Hash::make('password'),
                'role' => 'AGENT',
                'department_id' => $finance->id,
            ]
        );
    }
}
