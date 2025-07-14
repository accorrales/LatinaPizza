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
            $table->foreignId('promocion_id')->nullable()->constrained('promociones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('carrito_items', function (Blueprint $table) {
            $table->dropForeign(['promocion_id']);
            $table->dropColumn('promocion_id');
        });
    }
};
