<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisiscalidad', function (Blueprint $table) {
            $table->boolean('reactor1')->default(false)->after('filtro');
            $table->boolean('reactor2')->default(false)->after('reactor1');
            $table->boolean('reactor3')->default(false)->after('reactor2');
            $table->boolean('reactor4')->default(false)->after('reactor3');
        });

        // Migrar datos existentes del JSON a columnas booleanas
        $registros = DB::table('analisiscalidad')
            ->whereNotNull('reactor')
            ->where('reactor', '!=', '')
            ->get();

        foreach ($registros as $r) {
            $reactores = json_decode($r->reactor, true);
            if (!is_array($reactores)) continue;

            DB::table('analisiscalidad')->where('id', $r->id)->update([
                'reactor1' => in_array('Reactor#1', $reactores) ? 1 : 0,
                'reactor2' => in_array('Reactor#2', $reactores) ? 1 : 0,
                'reactor3' => in_array('Reactor#3', $reactores) ? 1 : 0,
                'reactor4' => in_array('Reactor#4', $reactores) ? 1 : 0,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('analisiscalidad', function (Blueprint $table) {
            $table->dropColumn(['reactor1', 'reactor2', 'reactor3', 'reactor4']);
        });
    }
};
