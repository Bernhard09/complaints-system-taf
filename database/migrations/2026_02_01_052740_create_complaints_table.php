<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('contract_number', 15);
            $table->string('complaint_reason', 100);
            $table->text('description');

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'SUBMITTED',
                'IN_REVIEW',
                'ASSIGNED',
                'IN_PROGRESS',
                'WAITING_USER',
                'WAITING_CONFIRMATION',
                'REALLOCATE_REQUESTED',
                'CANCELLED',
                'RESOLVED'
            ]);

            $table->string('sla_type', 10)->nullable();
            $table->timestamp('sla_deadline')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('agent_id');
            $table->index('user_id');
        });

        // PostgreSQL CHECK constraint
        DB::statement("
            ALTER TABLE complaints
            ADD CONSTRAINT chk_contract_number
            CHECK (contract_number ~ '^[0-9]{10,15}$')
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
