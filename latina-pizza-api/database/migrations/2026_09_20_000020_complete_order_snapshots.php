<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrito_items_promocion_detalles', function (Blueprint $table) {
            $table->foreignId('tamano_id')->nullable()->after('sabor_id')
                ->constrained('tamanos')->nullOnDelete();
        });

        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('pedido_id')
                ->constrained('productos')->nullOnDelete();
            $table->unsignedInteger('cantidad')->default(1)->after('masa_id');
        });

        Schema::table('detalle_pedido_promocion', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->after('promocion_id')
                ->constrained('productos')->nullOnDelete();
            $table->unsignedInteger('cantidad')->default(1)->after('masa_id');
            $table->decimal('precio_total', 10, 2)->default(0)->after('cantidad');
            $table->unsignedBigInteger('sabor_id')->nullable()->change();
            $table->unsignedBigInteger('tamano_id')->nullable()->change();
            $table->unsignedBigInteger('masa_id')->nullable()->change();
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->json('delivery_address_json')->nullable()->after('detalle_json');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', fn (Blueprint $table) => $table->dropColumn('delivery_address_json'));
        Schema::table('detalle_pedido_promocion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producto_id');
            $table->dropColumn(['cantidad', 'precio_total']);
        });
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producto_id');
            $table->dropColumn('cantidad');
        });
        Schema::table('carrito_items_promocion_detalles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tamano_id');
        });
    }
};
