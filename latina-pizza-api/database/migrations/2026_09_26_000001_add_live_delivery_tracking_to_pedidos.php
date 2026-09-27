<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // pedidos.estado is a varchar: it already accepts en_camino, no enum rewrite required.
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('delivery_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->float('delivery_accuracy')->nullable();
            // Match existing Eloquent timestamps: application timezone, ISO 8601 at the API boundary.
            $table->timestamp('delivery_recorded_at')->nullable();
            $table->timestamp('delivery_received_at')->nullable();
            $table->index(['delivery_user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['delivery_user_id', 'estado']);
            $table->dropConstrainedForeignId('delivery_user_id');
            $table->dropColumn(['delivery_latitude', 'delivery_longitude', 'delivery_accuracy', 'delivery_recorded_at', 'delivery_received_at']);
        });
    }
};
