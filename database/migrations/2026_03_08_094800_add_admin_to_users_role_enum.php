<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Laravel creates a CHECK constraint for enum columns on PostgreSQL.
        // We need to drop the old constraint and create a new one that includes 'ADMIN'.
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['USER'::text, 'AGENT'::text, 'SUPERVISOR'::text, 'ADMIN'::text]))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role::text = ANY (ARRAY['USER'::text, 'AGENT'::text, 'SUPERVISOR'::text]))");
    }
};
