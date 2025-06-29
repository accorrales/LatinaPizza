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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('metodo_pago')->default('efectivo'); // stripe, efectivo, sinpe, datafono
            $table->string('estado_pago')->default('pendiente'); // pagado o pendiente
        });
    }

    public function down()
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
            $table->dropColumn('estado_pago');
        });
    }
};
