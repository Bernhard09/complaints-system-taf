<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('complaints', function (Blueprint $table) {

            // kapan agent mulai bertanggung jawab
            $table->timestamp('assigned_at')
                    ->nullable()
                    ->after('agent_id');

            // SLA deadlines
            $table->timestamp('sla_response_deadline')
                    ->nullable()
                    ->after('assigned_at');

            $table->timestamp('sla_resolution_deadline')
                    ->nullable()
                    ->after('sla_response_deadline');

            // respon pertama agent
            $table->timestamp('first_response_at')
                    ->nullable()
                    ->after('sla_resolution_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_at',
                'sla_response_deadline',
                'sla_resolution_deadline',
                'first_response_at',
            ]);
        });
    }
};
