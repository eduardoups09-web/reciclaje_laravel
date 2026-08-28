<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('transportista', 'ruc')) {
            Schema::table('transportista', function (Blueprint $table) {
                $table->string('ruc', 100)->nullable()->after('transportistas');
            });
        }

        if (!Schema::hasColumn('transportista', 'placa')) {
            Schema::table('transportista', function (Blueprint $table) {
                $table->string('placa', 100)->nullable()->after('ruc');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('transportista', 'placa')) {
            Schema::table('transportista', function (Blueprint $table) {
                $table->dropColumn('placa');
            });
        }

        if (Schema::hasColumn('transportista', 'ruc')) {
            Schema::table('transportista', function (Blueprint $table) {
                $table->dropColumn('ruc');
            });
        }
    }
};
