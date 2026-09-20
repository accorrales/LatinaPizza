<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->deduplicateReviews();
        $this->mergeDuplicateCarts();
        $this->deduplicatePaymentReferences();

        Schema::table('resenas', function (Blueprint $table) {
            $table->unique(['user_id', 'sabor_id'], 'resenas_user_sabor_unique');
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->unique('payment_ref', 'pedidos_payment_ref_unique');
        });

        Schema::table('carritos', function (Blueprint $table) {
            $table->unique('user_id', 'carritos_user_unique');
        });
    }

    private function deduplicateReviews(): void
    {
        $duplicates = DB::table('resenas')
            ->select('user_id', 'sabor_id')
            ->groupBy('user_id', 'sabor_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('resenas')
                ->where('user_id', $duplicate->user_id)
                ->where('sabor_id', $duplicate->sabor_id)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('id');

            DB::table('resenas')->whereIn('id', $ids->skip(1))->delete();
        }
    }

    private function mergeDuplicateCarts(): void
    {
        $userIds = DB::table('carritos')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($userIds as $userId) {
            $cartIds = DB::table('carritos')->where('user_id', $userId)->orderBy('id')->pluck('id');
            $keeper = $cartIds->first();
            $duplicates = $cartIds->skip(1);

            DB::table('carrito_items')->whereIn('carrito_id', $duplicates)->update(['carrito_id' => $keeper]);

            foreach (DB::table('carrito_producto')->whereIn('carrito_id', $duplicates)->get() as $row) {
                $existing = DB::table('carrito_producto')
                    ->where('carrito_id', $keeper)
                    ->where('producto_id', $row->producto_id)
                    ->first();

                if ($existing) {
                    DB::table('carrito_producto')->where('id', $existing->id)->update([
                        'cantidad' => (int) $existing->cantidad + (int) $row->cantidad,
                        'precio_total' => (float) $existing->precio_total + (float) $row->precio_total,
                        'updated_at' => now(),
                    ]);
                    DB::table('carrito_producto')->where('id', $row->id)->delete();
                } else {
                    DB::table('carrito_producto')->where('id', $row->id)->update(['carrito_id' => $keeper]);
                }
            }

            DB::table('carritos')->whereIn('id', $duplicates)->delete();
        }
    }

    private function deduplicatePaymentReferences(): void
    {
        $references = DB::table('pedidos')
            ->select('payment_ref')
            ->whereNotNull('payment_ref')
            ->groupBy('payment_ref')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('payment_ref');

        foreach ($references as $reference) {
            $duplicateIds = DB::table('pedidos')
                ->where('payment_ref', $reference)
                ->orderBy('id')
                ->pluck('id')
                ->skip(1);

            DB::table('pedidos')->whereIn('id', $duplicateIds)->update(['payment_ref' => null]);
        }
    }

    public function down(): void
    {
        Schema::table('resenas', function (Blueprint $table) {
            $table->dropUnique('resenas_user_sabor_unique');
        });
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropUnique('pedidos_payment_ref_unique');
        });
        Schema::table('carritos', function (Blueprint $table) {
            $table->dropUnique('carritos_user_unique');
        });
    }
};
