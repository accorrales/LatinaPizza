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
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('promocion_componentes', function (Blueprint $table) {
            $table->dropForeign(['producto_id']);
            $table->dropColumn('producto_id');
        });
    }
};
