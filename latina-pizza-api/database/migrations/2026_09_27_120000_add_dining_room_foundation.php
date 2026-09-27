<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->restrictOnDelete();
            $table->string('numero', 40);
            $table->string('nombre')->nullable();
            $table->string('zona')->nullable();
            $table->unsignedSmallInteger('capacidad')->default(4);
            $table->string('estado', 30)->default('disponible');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['sucursal_id', 'numero']);
            $table->index(['sucursal_id', 'estado']);
        });

        Schema::create('mesa_sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mesa_id')->constrained('mesas')->restrictOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->restrictOnDelete();
            $table->foreignId('mesero_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado', 30)->default('abierta');
            $table->unsignedSmallInteger('personas')->default(1);
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['sucursal_id', 'estado']);
            $table->index(['mesa_id', 'estado']);
            $table->index(['mesero_user_id', 'estado']);
        });

        // PostgreSQL (producción) y SQLite (tests) soportan índices parciales.
        // Esto protege la invariantes incluso si dos procesos intentan abrir la misma mesa.
        DB::statement("CREATE UNIQUE INDEX mesa_sesiones_one_open_per_table ON mesa_sesiones (mesa_id) WHERE estado = 'abierta'");

        Schema::table('pedidos', function (Blueprint $table) {
            // Salón y mostrador deben poder registrar ventas sin obligar al cliente a crear una cuenta.
            $table->foreignId('user_id')->nullable()->change();

            // El canal describe dónde nació la venta; tipo_entrega/tipo_pedido conserva pickup/express/salón.
            $table->string('canal_venta', 30)->default('web')->after('tipo_pedido');
            $table->foreignId('mesa_sesion_id')->nullable()->after('canal_venta')
                ->constrained('mesa_sesiones')->restrictOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->after('mesa_sesion_id')
                ->constrained('users')->nullOnDelete();

            $table->index(['sucursal_id', 'canal_venta']);
        });
    }

    public function down(): void
    {
        // No convertimos user_id a NOT NULL si ya existen pedidos invitados: hacerlo
        // invalidaría datos reales. Preferimos detener el rollback antes de destruir datos.
        if (DB::table('pedidos')->whereNull('user_id')->exists()) {
            throw new RuntimeException(
                'No se puede revertir la base de salón mientras existan pedidos sin usuario asociado.'
            );
        }

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['sucursal_id', 'canal_venta']);
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropConstrainedForeignId('mesa_sesion_id');
            $table->dropColumn('canal_venta');
            $table->foreignId('user_id')->nullable(false)->change();
        });

        DB::statement('DROP INDEX IF EXISTS mesa_sesiones_one_open_per_table');
        Schema::dropIfExists('mesa_sesiones');
        Schema::dropIfExists('mesas');
    }
};
