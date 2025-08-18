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
        Schema::table('pedidos', function (Blueprint $table) {
            // Logística
            if (!Schema::hasColumn('pedidos', 'tipo_entrega')) {
                $table->string('tipo_entrega', 20)->nullable()->after('estado'); // 'pickup' | 'express'
            }
            if (!Schema::hasColumn('pedidos', 'direccion_usuario_id')) {
                $table->unsignedBigInteger('direccion_usuario_id')->nullable()
                      ->after('sucursal_id');
            }

            // Delivery
            if (!Schema::hasColumn('pedidos', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->nullable()
                      ->after('direccion_usuario_id');
            }
            if (!Schema::hasColumn('pedidos', 'delivery_currency')) {
                $table->string('delivery_currency', 8)->nullable()
                      ->after('delivery_fee');
            }
            if (!Schema::hasColumn('pedidos', 'delivery_distance_km')) {
                $table->decimal('delivery_distance_km', 8, 2)->nullable()
                      ->after('delivery_currency');
            }

            // Totales
            if (!Schema::hasColumn('pedidos', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable()
                      ->after('delivery_distance_km');
            }
            // Ojo: ya tienes total → no lo tocamos

            // Snapshot del carrito
            if (!Schema::hasColumn('pedidos', 'detalle_json')) {
                $table->json('detalle_json')->nullable()->after('total');
            }

            // (Opcional) FKs si quieres:
            // $table->foreign('direccion_usuario_id')->references('id')->on('direcciones_usuario');
            // $table->foreign('sucursal_id')->references('id')->on('sucursales');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Solo quitamos lo que agregamos
            foreach ([
                'tipo_entrega',
                'direccion_usuario_id',
                'delivery_fee',
                'delivery_currency',
                'delivery_distance_km',
                'subtotal',
                'detalle_json',
            ] as $col) {
                if (Schema::hasColumn('pedidos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
