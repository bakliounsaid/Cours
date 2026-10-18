<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Supabase expose le schéma public via son API REST (clé anon).
 * L'application n'utilise pas cette API : on active la RLS sans politique sur toutes
 * les tables, ce qui bloque l'API. Laravel, propriétaire des tables, n'est pas concerné.
 *
 * À reprendre pour chaque nouvelle table (ou relancer : la migration est idempotente).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->tables() as $table) {
            DB::statement(sprintf('ALTER TABLE %s ENABLE ROW LEVEL SECURITY', $this->quote($table)));
        }
    }

    public function down(): void
    {
        foreach ($this->tables() as $table) {
            DB::statement(sprintf('ALTER TABLE %s DISABLE ROW LEVEL SECURITY', $this->quote($table)));
        }
    }

    /**
     * @return list<string>
     */
    private function tables(): array
    {
        return DB::table('pg_tables')->where('schemaname', 'public')->pluck('tablename')->all();
    }

    private function quote(string $table): string
    {
        return 'public."'.str_replace('"', '""', $table).'"';
    }
};
