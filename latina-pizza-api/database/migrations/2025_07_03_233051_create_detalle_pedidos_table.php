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
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();

            // Relación al pedido principal
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');

            // Relación al sabor de pizza
            $table->foreignId('sabor_id')->constrained('sabores')->onDelete('cascade');

            // Relación al tamaño
            $table->foreignId('tamano_id')->constrained('tamanos')->onDelete('cascade');

            // Relación al tipo de masa
            $table->foreignId('masa_id')->nullable()->constrained('masas')->onDelete('set null');

            // Nota personalizada
            $table->text('nota_cliente')->nullable();

            // Precio total de esta pizza personalizada
            $table->decimal('precio_total', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
