<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            // Monto del envío (numeric(10,2) en Postgres)
            $table->decimal('delivery_fee', 10, 2)
                  ->nullable()
                  ->after('direccion_usuario_id');

            // Distancia usada para calcular el envío (km)
            $table->decimal('delivery_distance_km', 8, 2)
                  ->nullable()
                  ->after('delivery_fee');

            // Moneda (ej: ₡, CRC, USD)
            $table->string('delivery_currency', 8)
                  ->nullable()
                  ->after('delivery_distance_km');
        });
    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'delivery_distance_km', 'delivery_currency']);
        });
    }
};
