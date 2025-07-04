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
        Schema::create('tamanos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Pequeña, Mediana, Grande, etc.
            $table->decimal('precio_base', 8, 2); // Base para calcular el precio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tamanos');
    }
};
