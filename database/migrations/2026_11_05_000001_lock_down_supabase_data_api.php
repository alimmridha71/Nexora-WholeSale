<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
| Supabase exposes the `public` schema through its Data API (PostgREST) to
| the `anon` and `authenticated` roles. Nexora Wholesale never uses that API:
| Laravel connects directly as the table owner. So we:
|   1. enable Row Level Security on every table with NO policies, and
|   2. revoke all table privileges from anon / authenticated,
| which makes the tables unreachable with the publishable/anon key.
| The table owner (Laravel's connection) is unaffected because RLS is not FORCEd.
|
| No-op on MySQL / SQLite and on plain PostgreSQL without Supabase roles.
*/
return new class extends Migration
{
    private const TABLES = [
        'users', 'password_reset_tokens', 'sessions',
        'cache', 'cache_locks', 'products', 'bulk_prices', 'quotes', 'migrations',
    ];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLES as $table) {
            DB::statement("ALTER TABLE \"{$table}\" ENABLE ROW LEVEL SECURITY");
        }

        DB::unprepared(<<<'SQL'
            DO $$
            DECLARE r text;
            BEGIN
                FOREACH r IN ARRAY ARRAY['anon', 'authenticated'] LOOP
                    IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = r) THEN
                        EXECUTE format('REVOKE ALL ON ALL TABLES IN SCHEMA public FROM %I', r);
                        EXECUTE format('REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM %I', r);
                        EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON TABLES FROM %I', r);
                        EXECUTE format('ALTER DEFAULT PRIVILEGES IN SCHEMA public REVOKE ALL ON SEQUENCES FROM %I', r);
                    END IF;
                END LOOP;
            END
            $$;
            SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        foreach (self::TABLES as $table) {
            DB::statement("ALTER TABLE \"{$table}\" DISABLE ROW LEVEL SECURITY");
        }
    }
};
