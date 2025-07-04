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
        Schema::create('detalle_pedido_promocion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');
            $table->foreignId('sabor_id')->constrained('sabores')->onDelete('cascade');
            $table->foreignId('tamano_id')->constrained('tamanos')->onDelete('cascade');
            $table->foreignId('masa_id')->constrained('masas')->onDelete('cascade');
            $table->string('nota_cliente')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_promocion');
    }
};
