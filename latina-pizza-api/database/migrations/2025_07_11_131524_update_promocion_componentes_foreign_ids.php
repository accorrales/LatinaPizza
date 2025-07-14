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
        Schema::table('promocion_componentes', function (Blueprint $table) {
            // Eliminamos columna antigua
            $table->dropColumn('tamano');

            // Agregamos relaciones reales
            $table->foreignId('tamano_id')->constrained('tamanos')->onDelete('cascade');
            $table->foreignId('masa_id')->constrained('masas')->onDelete('cascade');
            $table->foreignId('sabor_id')->constrained('sabores')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('promocion_componentes', function (Blueprint $table) {
            $table->dropForeign(['tamano_id']);
            $table->dropForeign(['masa_id']);
            $table->dropForeign(['sabor_id']);

            $table->dropColumn(['tamano_id', 'masa_id', 'sabor_id']);
            $table->string('tamano')->nullable(); // Restauramos si se hace rollback
        });
    }
};
