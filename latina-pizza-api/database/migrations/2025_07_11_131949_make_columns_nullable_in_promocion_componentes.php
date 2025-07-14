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
        Schema::table('promocion_componentes', function (Blueprint $table) {
            $table->unsignedBigInteger('tamano_id')->nullable()->change();
            $table->unsignedBigInteger('masa_id')->nullable()->change();
            $table->unsignedBigInteger('sabor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('promocion_componentes', function (Blueprint $table) {
            $table->unsignedBigInteger('tamano_id')->nullable(false)->change();
            $table->unsignedBigInteger('masa_id')->nullable(false)->change();
            $table->unsignedBigInteger('sabor_id')->nullable(false)->change();
        });
    }
};
