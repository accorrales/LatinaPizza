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
            $table->boolean('incluye_bebida')->default(false)->after('precio_total');
        });
    }

    public function down()
    {
        Schema::table('promociones', function (Blueprint $table) {
            $table->dropColumn('incluye_bebida');
        });
    }
};
