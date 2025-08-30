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
        Schema::table('pedidos', function (Blueprint $t) {
            // índices útiles para el panel
            $t->index('sucursal_id');
            $t->index(['kitchen_status','sucursal_id']);
            $t->index('created_at');
            $t->index('paid_at');

            // defaults de seguridad (si tu motor lo permite)
            // Nota: usar change() requiere que la columna soporte default en tu DB.
            try {
                $t->boolean('priority')->default(false)->change();
            } catch (\Throwable $e) {}
            try {
                $t->string('kitchen_status', 255)->default('nuevo')->change();
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $t) {
            $t->dropIndex(['pedidos_sucursal_id_index']);
            $t->dropIndex(['pedidos_kitchen_status_sucursal_id_index']);
            $t->dropIndex(['pedidos_created_at_index']);
            $t->dropIndex(['pedidos_paid_at_index']);
        });
    }
};
