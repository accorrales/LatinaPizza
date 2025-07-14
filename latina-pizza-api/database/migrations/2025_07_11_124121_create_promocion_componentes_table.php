<?php

use App\Models\Promocion;
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
        Schema::create('promocion_componentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promocion_id')->constrained('promociones')->onDelete('cascade');
            $table->enum('tipo', ['pizza', 'bebida']); // lo que incluye
            $table->string('tamano')->nullable(); // ejemplo: grande, mediana, etc. Solo para pizzas
            $table->integer('cantidad')->default(1); // cuántos productos de ese tipo incluye
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_componentes');
    }
};
