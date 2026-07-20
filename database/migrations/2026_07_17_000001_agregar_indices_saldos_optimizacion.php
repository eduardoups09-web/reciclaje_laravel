<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addIndexIfNotExists(string $table, array $columns, ?string $name = null): void
    {
        $indexName = $name ?? implode('_', $columns) . '_index';
        $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        if (empty($exists)) {
            $colList = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
            DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` ({$colList})");
        }
    }

    public function up(): void
    {
        $this->addIndexIfNotExists('ingresosinventarios', ['FechaCab']);
        $this->addIndexIfNotExists('ingresosinventarios', ['Producto']);
        $this->addIndexIfNotExists('ingresosinventarios', ['FechaCab', 'Producto'], 'ingresos_fechaproducto_idx');

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
        DB::statement("DROP INDEX IF EXISTS `ingresosinventarios_fechacab_index` ON `ingresosinventarios`");
        DB::statement("DROP INDEX IF EXISTS `ingresosinventarios_producto_index` ON `ingresosinventarios`");
        DB::statement("DROP INDEX IF EXISTS `ingresos_fechaproducto_idx` ON `ingresosinventarios`");

        DB::statement("DROP INDEX IF EXISTS `bodega_fechainicio_index` ON `bodega`");
        DB::statement("DROP INDEX IF EXISTS `bodega_is_deleted_index` ON `bodega`");
        DB::statement("DROP INDEX IF EXISTS `bodega_del_fecha_idx` ON `bodega`");

        DB::statement("DROP INDEX IF EXISTS `mpnacional_fechanacional_index` ON `mpnacional`");
        DB::statement("DROP INDEX IF EXISTS `mpnacional_is_deleted_index` ON `mpnacional`");
        DB::statement("DROP INDEX IF EXISTS `mpnac_idx_comp` ON `mpnacional`");

        DB::statement("DROP INDEX IF EXISTS `mpimport_fechaimport_index` ON `mpimport`");
        DB::statement("DROP INDEX IF EXISTS `mpimport_is_deleted_index` ON `mpimport`");
        DB::statement("DROP INDEX IF EXISTS `mpimp_idx_comp` ON `mpimport`");

        DB::statement("DROP INDEX IF EXISTS `saldosinsert_fechasaldoinsert_index` ON `saldosinsert`");
        DB::statement("DROP INDEX IF EXISTS `saldos_fecha_turno_idx` ON `saldosinsert`");
    }
};
