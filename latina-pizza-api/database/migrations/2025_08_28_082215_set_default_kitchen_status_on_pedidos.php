<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('pedidos')->whereNull('kitchen_status')->update(['kitchen_status' => 'nuevo']);
        DB::statement("ALTER TABLE pedidos ALTER COLUMN kitchen_status SET DEFAULT 'nuevo'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE pedidos ALTER COLUMN kitchen_status DROP DEFAULT");
    }
};
