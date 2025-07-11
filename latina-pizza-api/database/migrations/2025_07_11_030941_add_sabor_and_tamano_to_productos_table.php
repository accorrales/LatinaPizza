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
        Schema::table('productos', function (Blueprint $table) {
            $table->foreignId('sabor_id')->nullable()->constrained('sabores')->onDelete('cascade');
            $table->foreignId('tamano_id')->nullable()->constrained('tamanos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign(['sabor_id']);
            $table->dropForeign(['tamano_id']);
            $table->dropColumn(['sabor_id', 'tamano_id']);
        });
    }
};
