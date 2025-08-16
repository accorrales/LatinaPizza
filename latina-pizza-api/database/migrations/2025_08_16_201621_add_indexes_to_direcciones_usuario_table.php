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
        Schema::table('direcciones_usuario', function (Blueprint $table) {
            // Index para filtrar rápido por dueño
            $table->index('user_id', 'direcciones_usuario_user_id_index');

            // Index compuesto para posibles búsquedas por ubicación (lat/long)
            $table->index(['latitud', 'longitud'], 'direcciones_usuario_latitud_longitud_index');
        });
    }

    public function down(): void
    {
        Schema::table('direcciones_usuario', function (Blueprint $table) {
            $table->dropIndex('direcciones_usuario_user_id_index');
            $table->dropIndex('direcciones_usuario_latitud_longitud_index');
        });
    }
};
