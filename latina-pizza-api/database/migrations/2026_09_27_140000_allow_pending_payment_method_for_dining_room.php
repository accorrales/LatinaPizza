<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_pedidos_metodo_pago');
        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT chk_pedidos_metodo_pago CHECK (metodo_pago IN ('pendiente','efectivo','datafono','stripe'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (DB::table('pedidos')->where('metodo_pago', 'pendiente')->exists()) {
            throw new RuntimeException(
                'No se puede revertir la compatibilidad de salón mientras existan pedidos con método de pago pendiente.'
            );
        }

        DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_pedidos_metodo_pago');
        DB::statement("ALTER TABLE pedidos ADD CONSTRAINT chk_pedidos_metodo_pago CHECK (metodo_pago IN ('efectivo','datafono','stripe'))");
    }
};
