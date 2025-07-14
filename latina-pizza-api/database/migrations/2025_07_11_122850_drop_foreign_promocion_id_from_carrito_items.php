<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('carrito_items', function (Blueprint $table) {
        $table->dropForeign(['promocion_id']); // Elimina la restricción
        $table->dropColumn('promocion_id');     // Opcional: elimina la columna
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('carrito_items', function (Blueprint $table) {
        $table->unsignedBigInteger('promocion_id')->nullable();

        $table->foreign('promocion_id')
              ->references('id')->on('promociones')
              ->onDelete('set null');
    });
}

};
