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
            // proveedor del pago (p.ej., stripe, cash, pos)
            $table->string('payment_provider')->nullable()->after('metodo_pago');
            // referencia (p.ej., payment_intent_id de stripe)
            $table->string('payment_ref')->nullable()->after('payment_provider');
            // estado del pago (paid|pending|failed|refunded)
            $table->string('payment_status')->default('pending')->after('payment_ref');
            // fecha/hora de liquidación
            $table->timestamp('paid_at')->nullable()->after('payment_status');

            // índice útil para búsquedas por referencia
            $table->index('payment_ref');
            $table->index(['payment_status', 'payment_provider']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['payment_ref']);
            $table->dropIndex(['payment_status', 'payment_provider']);
            $table->dropColumn(['payment_provider', 'payment_ref', 'payment_status', 'paid_at']);
        });
    }
};
