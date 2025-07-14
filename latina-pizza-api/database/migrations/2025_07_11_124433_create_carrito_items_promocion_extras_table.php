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
        Schema::create('carrito_items_promocion_extras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('detalle_id')
                ->constrained('carrito_items_promocion_detalles')
                ->onDelete('cascade');

            $table->foreignId('extra_id')
                ->constrained('extras')
                ->onDelete('cascade');

            $table->decimal('precio', 10, 2); // Precio del extra según el tamaño

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrito_items_promocion_extras');
    }
};
