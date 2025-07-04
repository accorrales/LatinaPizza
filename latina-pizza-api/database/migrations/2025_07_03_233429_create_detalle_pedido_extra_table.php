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
        Schema::create('detalle_pedido_extra', function (Blueprint $table) {
            $table->id();

            // Relación al detalle de pedido (pizza personalizada)
            $table->foreignId('detalle_pedido_id')->constrained('detalle_pedidos')->onDelete('cascade');

            // Relación al producto extra (ej: extra queso, champiñones)
            $table->foreignId('extra_id')->constrained('extras')->onDelete('cascade');

            // Precio del extra calculado según el tamaño de la pizza
            $table->decimal('precio_extra', 8, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_extra');
    }
};
