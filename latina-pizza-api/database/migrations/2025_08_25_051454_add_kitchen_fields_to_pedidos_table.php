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
            // ⚠️ Si alguna columna ya existe, omite el add para evitar errores.
            if (!Schema::hasColumn('pedidos', 'kitchen_status')) {
                $table->string('kitchen_status')->default('nuevo')->index(); // nuevo|preparacion|listo|entregado|cancelado
            }
            if (!Schema::hasColumn('pedidos', 'priority')) {
                $table->boolean('priority')->default(false)->index();
            }
            if (!Schema::hasColumn('pedidos', 'sla_minutes')) {
                $table->unsignedSmallInteger('sla_minutes')->nullable();
            }
            if (!Schema::hasColumn('pedidos', 'promised_at')) {
                $table->timestamp('promised_at')->nullable()->index();
            }
            if (!Schema::hasColumn('pedidos', 'ready_at')) {
                $table->timestamp('ready_at')->nullable()->index();
            }
            if (!Schema::hasColumn('pedidos', 'taken_by_user_id')) {
                $table->foreignId('taken_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pedidos', 'kitchen_notes')) {
                $table->text('kitchen_notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Primero FK (si existe) y luego la columna
            if (Schema::hasColumn('pedidos', 'taken_by_user_id')) {
                // dropConstrainedForeignId maneja FK+columna; si tu versión no lo soporta, usa dropForeign + dropColumn
                try {
                    $table->dropConstrainedForeignId('taken_by_user_id');
                } catch (\Throwable $e) {
                    // Fallback por si la clave tiene nombre distinto
                    $table->dropForeign(['taken_by_user_id']);
                    $table->dropColumn('taken_by_user_id');
                }
            }
            foreach (['kitchen_status','priority','sla_minutes','promised_at','ready_at','kitchen_notes'] as $col) {
                if (Schema::hasColumn('pedidos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
