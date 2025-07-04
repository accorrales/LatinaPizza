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
        Schema::create('masas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // Delgada, Gruesa, Pan, etc.
            $table->decimal('precio_extra', 8, 2)->default(0); // Costo adicional
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masas');
    }
};
