<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('complaint_assignments', function (Blueprint $table) {
            $table->foreignId('to_department_id')->nullable()->after('to_agent_id')
                  ->constrained('departments')->nullOnDelete();
            $table->string('status')->default('PENDING')->after('reason');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('rejection_reason');
            $table->timestamp('sla_resolution_deadline')->nullable()->after('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('complaint_assignments', function (Blueprint $table) {
            $table->dropForeign(['to_department_id']);
            $table->dropColumn([
                'to_department_id',
                'status',
                'rejection_reason',
                'confirmed_at',
                'sla_resolution_deadline',
            ]);
        });
    }
};
