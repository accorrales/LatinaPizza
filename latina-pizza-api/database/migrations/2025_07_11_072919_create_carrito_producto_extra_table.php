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
        Schema::create('carrito_producto_extra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrito_id')->constrained()->onDelete('cascade');
            $table->foreignId('producto_id')->constrained()->onDelete('cascade');
            $table->foreignId('extra_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['carrito_id', 'producto_id', 'extra_id'], 'carrito_producto_extra_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('carrito_producto_extra');
    }
};
