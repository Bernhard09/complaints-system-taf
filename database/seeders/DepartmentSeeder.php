<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['FINANCE', 'COLLECTION', 'LEGAL', 'UMUM'] as $name) {
            Department::firstOrCreate(['name' => $name]);
        }
    }
}
