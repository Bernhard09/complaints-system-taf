<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing check constraint
        DB::statement('ALTER TABLE complaints DROP CONSTRAINT IF EXISTS complaints_status_check');

        // Add the new check constraint with the additional statuses
        DB::statement("
            ALTER TABLE complaints ADD CONSTRAINT complaints_status_check CHECK (
                status::text = ANY (ARRAY[
                    'SUBMITTED'::character varying, 
                    'IN_REVIEW'::character varying, 
                    'ASSIGNED'::character varying, 
                    'IN_PROGRESS'::character varying, 
                    'WAITING_USER'::character varying, 
                    'WAITING_CONFIRMATION'::character varying, 
                    'REALLOCATE_REQUESTED'::character varying, 
                    'PENDING_REASSIGN'::character varying,
                    'CLOSED'::character varying,
                    'CANCELLED'::character varying, 
                    'RESOLVED'::character varying
                ]::text[])
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE complaints DROP CONSTRAINT IF EXISTS complaints_status_check');

        DB::statement("
            ALTER TABLE complaints ADD CONSTRAINT complaints_status_check CHECK (
                status::text = ANY (ARRAY[
                    'SUBMITTED'::character varying, 
                    'IN_REVIEW'::character varying, 
                    'ASSIGNED'::character varying, 
                    'IN_PROGRESS'::character varying, 
                    'WAITING_USER'::character varying, 
                    'WAITING_CONFIRMATION'::character varying, 
                    'REALLOCATE_REQUESTED'::character varying, 
                    'CANCELLED'::character varying, 
                    'RESOLVED'::character varying
                ]::text[])
            )
        ");
    }
};
