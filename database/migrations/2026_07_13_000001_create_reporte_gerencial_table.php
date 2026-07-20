<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporte_gerencial', function (Blueprint $table) {
            $table->id();
            $table->integer('mes');
            $table->integer('anio');
            $table->decimal('saldo_total', 14, 2)->default(0);
            $table->decimal('total_recepcion', 14, 2)->default(0);
            $table->decimal('consumo', 14, 2)->default(0);
            $table->decimal('maquila_enviada', 14, 2)->default(0);
            $table->decimal('maquila_recibida', 14, 2)->default(0);
            $table->integer('is_deleted')->default(0);
            $table->timestamps();

            $table->unique(['mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporte_gerencial');
    }
};
