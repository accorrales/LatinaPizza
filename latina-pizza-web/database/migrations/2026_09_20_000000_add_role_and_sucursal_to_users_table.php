<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $needsRole = ! Schema::hasColumn('users', 'role');
        $needsSucursal = ! Schema::hasColumn('users', 'sucursal_id');

        Schema::table('users', function (Blueprint $table) use ($needsRole, $needsSucursal) {
            if ($needsRole) {
                $table->string('role')->default('cliente')->index();
            }

            if ($needsSucursal) {
                $table->unsignedBigInteger('sucursal_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        $hasSucursal = Schema::hasColumn('users', 'sucursal_id');
        $hasRole = Schema::hasColumn('users', 'role');

        Schema::table('users', function (Blueprint $table) use ($hasSucursal, $hasRole) {
            if ($hasSucursal) {
                $table->dropColumn('sucursal_id');
            }

            if ($hasRole) {
                $table->dropColumn('role');
            }
        });
    }
};
