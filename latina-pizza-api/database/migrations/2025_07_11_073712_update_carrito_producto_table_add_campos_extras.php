<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('carrito_producto', function (Blueprint $table) {
            $table->foreignId('masa_id')->nullable()->constrained('masas')->onDelete('set null');
            $table->string('nota_cliente')->nullable();
            $table->decimal('precio_total', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
