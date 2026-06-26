<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analisiscalidad', function (Blueprint $table) {
            $table->decimal('pi', 10, 2)->nullable()->after('humedad');
            $table->decimal('pf', 10, 2)->nullable()->after('pi');
        });
    }

    public function down(): void
    {
        Schema::table('analisiscalidad', function (Blueprint $table) {
            $table->dropColumn(['pi', 'pf']);
        });
    }
};
