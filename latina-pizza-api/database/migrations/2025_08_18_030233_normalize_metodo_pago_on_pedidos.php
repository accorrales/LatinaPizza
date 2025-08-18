<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Sanea datos existentes (lowercase y sin acentos/espacios)
        //    Mapea posibles variantes al set canónico.
        //    Ajusta los WHEN si has usado otros valores.
        DB::statement("
            UPDATE pedidos
            SET metodo_pago = CASE
                WHEN trim(lower(unaccent(metodo_pago))) IN ('efectivo') THEN 'efectivo'
                WHEN trim(lower(unaccent(metodo_pago))) IN ('datafono', 'datufono', 'pos', 'terminal', 'tarjeta en pos') THEN 'datafono'
                WHEN trim(lower(unaccent(metodo_pago))) IN ('stripe', 'tarjeta', 'creditcard', 'card') THEN 'stripe'
                ELSE 'efectivo' -- fallback seguro; cámbialo si prefieres 'datafono'
            END
        ");

        // 2) Borra constraint anterior si ya existía (idempotente)
        try {
            DB::statement("ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_pedidos_metodo_pago");
        } catch (\Throwable $e) {}

        // 3) Crea CHECK constraint para permitir solo 3 valores
        DB::statement("
            ALTER TABLE pedidos
            ADD CONSTRAINT chk_pedidos_metodo_pago
            CHECK (metodo_pago IN ('efectivo','datafono','stripe'))
        ");

        // 4) (Opcional) índice para filtros/agrupaciones
        DB::statement("CREATE INDEX IF NOT EXISTS idx_pedidos_metodo_pago ON pedidos (metodo_pago)");
    }

    public function down(): void
    {
        // Quita constraint e índice
        try { DB::statement("ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_pedidos_metodo_pago"); } catch (\Throwable $e) {}
        try { DB::statement("DROP INDEX IF EXISTS idx_pedidos_metodo_pago"); } catch (\Throwable $e) {}
        // No revertimos el saneo (no suele hacer falta)
    }
};
