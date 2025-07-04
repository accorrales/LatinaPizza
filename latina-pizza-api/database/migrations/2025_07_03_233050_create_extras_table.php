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
        Schema::create('extras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio_pequena', 8, 2)->default(0);
            $table->decimal('precio_mediana', 8, 2)->default(0);
            $table->decimal('precio_grande', 8, 2)->default(0);
            $table->decimal('precio_extragrande', 8, 2)->default(0);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extras');
    }
};
