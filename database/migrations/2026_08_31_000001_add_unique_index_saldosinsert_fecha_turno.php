<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE saldosinsert ADD UNIQUE INDEX uq_fechaturno (fechasaldoinsert, turnosaldoinsert)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE saldosinsert DROP INDEX uq_fechaturno');
    }
};
