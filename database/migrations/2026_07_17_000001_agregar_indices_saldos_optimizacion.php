<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfNotExists(string $table, array $columns, ?string $name = null): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $indexName = $name ?? implode('_', $columns) . '_index';
        $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (empty($exists)) {
            $colList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
            DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` ({$colList})");
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::statement("DROP INDEX IF EXISTS `{$index}` ON `{$table}`");
    }

    public function up(): void
    {
        $this->addIndexIfNotExists('ingresosInventarios', ['FechaCab']);
        $this->addIndexIfNotExists('ingresosInventarios', ['Producto']);
        $this->addIndexIfNotExists('ingresosInventarios', ['FechaCab', 'Producto'], 'ingresos_fechaproducto_idx');

        $this->addIndexIfNotExists('bodega', ['fechainicio']);
        $this->addIndexIfNotExists('bodega', ['is_deleted']);
        $this->addIndexIfNotExists('bodega', ['is_deleted', 'fechainicio'], 'bodega_del_fecha_idx');

        $this->addIndexIfNotExists('mpnacional', ['fechanacional']);
        $this->addIndexIfNotExists('mpnacional', ['is_deleted']);
        $this->addIndexIfNotExists('mpnacional', ['is_deleted', 'fechanacional', 'turnonacional', 'bateriatipo'], 'mpnac_idx_comp');

        $this->addIndexIfNotExists('mpimport', ['fechaimport']);
        $this->addIndexIfNotExists('mpimport', ['is_deleted']);
        $this->addIndexIfNotExists('mpimport', ['is_deleted', 'fechaimport', 'turnoimport', 'bateriatipoimport'], 'mpimp_idx_comp');

        $this->addIndexIfNotExists('saldosinsert', ['fechasaldoinsert']);
        $this->addIndexIfNotExists('saldosinsert', ['fechasaldoinsert', 'turnosaldoinsert'], 'saldos_fecha_turno_idx');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('ingresosInventarios', 'ingresosInventarios_fechacab_index');
        $this->dropIndexIfExists('ingresosInventarios', 'ingresosInventarios_producto_index');
        $this->dropIndexIfExists('ingresosInventarios', 'ingresos_fechaproducto_idx');

        $this->dropIndexIfExists('bodega', 'bodega_fechainicio_index');
        $this->dropIndexIfExists('bodega', 'bodega_is_deleted_index');
        $this->dropIndexIfExists('bodega', 'bodega_del_fecha_idx');

        $this->dropIndexIfExists('mpnacional', 'mpnacional_fechanacional_index');
        $this->dropIndexIfExists('mpnacional', 'mpnacional_is_deleted_index');
        $this->dropIndexIfExists('mpnacional', 'mpnac_idx_comp');

        $this->dropIndexIfExists('mpimport', 'mpimport_fechaimport_index');
        $this->dropIndexIfExists('mpimport', 'mpimport_is_deleted_index');
        $this->dropIndexIfExists('mpimport', 'mpimp_idx_comp');

        $this->dropIndexIfExists('saldosinsert', 'saldosinsert_fechasaldoinsert_index');
        $this->dropIndexIfExists('saldosinsert', 'saldos_fecha_turno_idx');
    }
};
