<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carritos', function (Blueprint $t) {
            // 'pickup' | 'express' (lo validamos en app; lo dejamos nullable para no romper datos viejos)
            $t->string('tipo_entrega', 10)->nullable()->after('user_id');

            // Si el carrito es pickup → apunta a una sucursal
            $t->foreignId('sucursal_id')
                ->nullable()
                ->after('tipo_entrega')
                ->constrained('sucursales')
                ->nullOnDelete();

            // Si el carrito es express → apunta a una dirección del usuario
            $t->foreignId('direccion_usuario_id')
                ->nullable()
                ->after('sucursal_id')
                ->constrained('direcciones_usuario')
                ->nullOnDelete();
        });

        // (Opcional) Si querés forzar los valores válidos a nivel DB en Postgres:
        // DB::statement("ALTER TABLE carritos ADD CONSTRAINT carritos_tipo_entrega_check CHECK (tipo_entrega IN ('pickup','express'));");
    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $t) {
            // Elimina FKs y columnas en orden inverso
            $t->dropConstrainedForeignId('direccion_usuario_id');
            $t->dropConstrainedForeignId('sucursal_id');
            $t->dropColumn('tipo_entrega');
        });

        // (Si agregaste el CHECK opcional)
        // DB::statement("ALTER TABLE carritos DROP CONSTRAINT IF EXISTS carritos_tipo_entrega_check;");
    }
};
