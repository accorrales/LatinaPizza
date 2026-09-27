<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->foreignId('sabor_id')->nullable()->change();
            $table->foreignId('tamano_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->foreignId('sabor_id')->nullable(false)->change();
            $table->foreignId('tamano_id')->nullable(false)->change();
        });
    }
};
