<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'USER')->first();
        $agent = User::where('role', 'AGENT')->first();

        Complaint::create([
            'user_id' => $user->id,
            'contract_number' => '1234567890',
            'complaint_reason' => 'Test Complaint',
            'description' => 'Seeder generated complaint',
            'status' => 'IN_PROGRESS',
            'department_id' => $agent->department_id,
            'agent_id' => $agent->id,
            'assigned_at' => now()->subMinutes(10),
        ]);
    }
}
