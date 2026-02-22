<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();

            $table->foreignId('from_agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_agent_id')->constrained('users')->cascadeOnDelete();

            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();

            $table->text('reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_assignments_tale');
    }
};
