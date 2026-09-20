<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE pedidos
                SET metodo_pago = CASE
                    WHEN trim(lower(translate(metodo_pago, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU'))) IN ('efectivo') THEN 'efectivo'
                    WHEN trim(lower(translate(metodo_pago, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU'))) IN ('datafono', 'datufono', 'pos', 'terminal', 'tarjeta en pos') THEN 'datafono'
                    WHEN trim(lower(translate(metodo_pago, 'áéíóúÁÉÍÓÚ', 'aeiouAEIOU'))) IN ('stripe', 'tarjeta', 'creditcard', 'card') THEN 'stripe'
                    ELSE 'efectivo'
                END
            ");
        } else {
            DB::table('pedidos')->select('id', 'metodo_pago')->orderBy('id')->get()->each(function ($pedido): void {
                $value = strtolower(trim(Str::ascii((string) $pedido->metodo_pago)));
                $normalized = match ($value) {
                    'datafono', 'datufono', 'pos', 'terminal', 'tarjeta en pos' => 'datafono',
                    'stripe', 'tarjeta', 'creditcard', 'card' => 'stripe',
                    default => 'efectivo',
                };
                DB::table('pedidos')->where('id', $pedido->id)->update(['metodo_pago' => $normalized]);
            });
        }

        // 2) Borra constraint anterior si ya existía (idempotente)
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_pedidos_metodo_pago');
            DB::statement("ALTER TABLE pedidos ADD CONSTRAINT chk_pedidos_metodo_pago CHECK (metodo_pago IN ('efectivo','datafono','stripe'))");
            DB::statement('CREATE INDEX IF NOT EXISTS idx_pedidos_metodo_pago ON pedidos (metodo_pago)');
        } elseif (!Schema::hasIndex('pedidos', 'idx_pedidos_metodo_pago')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->index('metodo_pago', 'idx_pedidos_metodo_pago'));
        }
    }

    public function down(): void
    {
        // Quita constraint e índice
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE pedidos DROP CONSTRAINT IF EXISTS chk_pedidos_metodo_pago');
        }
        if (Schema::hasIndex('pedidos', 'idx_pedidos_metodo_pago')) {
            Schema::table('pedidos', fn (Blueprint $table) => $table->dropIndex('idx_pedidos_metodo_pago'));
        }
        // No revertimos el saneo (no suele hacer falta)
    }
};
