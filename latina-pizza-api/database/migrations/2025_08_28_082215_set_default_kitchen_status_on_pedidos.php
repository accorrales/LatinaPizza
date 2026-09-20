<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('pedidos')->whereNull('kitchen_status')->update(['kitchen_status' => 'nuevo']);
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('kitchen_status')->default('nuevo')->change();
        });
    }

    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('kitchen_status')->nullable()->default(null)->change();
        });
    }
};
