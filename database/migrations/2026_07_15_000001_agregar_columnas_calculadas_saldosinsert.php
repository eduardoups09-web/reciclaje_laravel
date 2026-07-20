<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saldosinsert', function (Blueprint $table) {
            $table->decimal('total_recepcion', 14, 2)->default(0)->after('saldototalinsertUPS');
            $table->decimal('recepcion_nacional_automotriz', 14, 2)->default(0)->after('total_recepcion');
            $table->decimal('recepcion_nacional_ups', 14, 2)->default(0)->after('recepcion_nacional_automotriz');
            $table->decimal('recepcion_importada_automotriz', 14, 2)->default(0)->after('recepcion_nacional_ups');
            $table->decimal('recepcion_importada_ups', 14, 2)->default(0)->after('recepcion_importada_automotriz');
            $table->decimal('bateria_nacional_automotriz', 14, 2)->default(0)->after('recepcion_importada_ups');
            $table->decimal('bateria_nacional_ups', 14, 2)->default(0)->after('bateria_nacional_automotriz');
            $table->decimal('bateria_importada_automotriz', 14, 2)->default(0)->after('bateria_nacional_ups');
            $table->decimal('bateria_importada_ups', 14, 2)->default(0)->after('bateria_importada_automotriz');
            $table->decimal('consumo', 14, 2)->default(0)->after('bateria_importada_ups');
            $table->decimal('maquila_enviada', 14, 2)->default(0)->after('consumo');
            $table->decimal('maquila_recibida', 14, 2)->default(0)->after('maquila_enviada');
            $table->decimal('saldo_cierre', 14, 2)->default(0)->after('maquila_recibida');
            $table->decimal('saldo_cierre_automotriz', 14, 2)->default(0)->after('saldo_cierre');
            $table->decimal('saldo_cierre_ups', 14, 2)->default(0)->after('saldo_cierre_automotriz');
        });
    }

    public function down(): void
    {
        Schema::table('saldosinsert', function (Blueprint $table) {
            $table->dropColumn([
                'total_recepcion',
                'recepcion_nacional_automotriz',
                'recepcion_nacional_ups',
                'recepcion_importada_automotriz',
                'recepcion_importada_ups',
                'bateria_nacional_automotriz',
                'bateria_nacional_ups',
                'bateria_importada_automotriz',
                'bateria_importada_ups',
                'consumo',
                'maquila_enviada',
                'maquila_recibida',
                'saldo_cierre',
                'saldo_cierre_automotriz',
                'saldo_cierre_ups',
            ]);
        });
    }
};
