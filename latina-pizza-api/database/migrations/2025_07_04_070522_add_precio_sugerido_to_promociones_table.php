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
        Schema::table('promociones', function (Blueprint $table) {
            $table->decimal('precio_sugerido', 10, 2)->nullable()->after('precio_total');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promociones', function (Blueprint $table) {
            $table->dropColumn('precio_sugerido');
        });
    }
};
