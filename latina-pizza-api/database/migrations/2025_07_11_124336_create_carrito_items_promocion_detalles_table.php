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
        Schema::create('carrito_items_promocion_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('carrito_item_id')->constrained('carrito_items')->onDelete('cascade');

            $table->enum('tipo', ['pizza', 'bebida']); // Tipo dentro de la promo

            // Para pizza
            $table->foreignId('sabor_id')->nullable()->constrained('sabores')->nullOnDelete();
            $table->foreignId('masa_id')->nullable()->constrained('masas')->nullOnDelete();
            $table->text('nota_cliente')->nullable();

            // Para bebida (u otros)
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrito_items_promocion_detalles');
    }
};
