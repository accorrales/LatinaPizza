<?php

namespace Database\Seeders;

use App\Models\Masa;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Tamano;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PromocionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $tamano = Tamano::where('nombre', 'Mediana')->firstOrFail();
            $masa = Masa::where('tipo', 'Tradicional')->firstOrFail();
            $bebida = Producto::whereHas('categoria', function ($query): void {
                $query->where('nombre', 'Bebidas');
            })->orderBy('id')->firstOrFail();

            $promocion = Promocion::updateOrCreate(
                ['nombre' => 'Promo 2 Pizzas + Refresco'],
                [
                    'descripcion' => 'Dos pizzas medianas y un refresco.',
                    'precio_total' => 11500,
                    'precio_sugerido' => 11500,
                    'imagen' => null,
                    'incluye_bebida' => true,
                ]
            );

            $promocion->componentes()->delete();
            $promocion->componentes()->createMany([
                [
                    'tipo' => 'pizza',
                    'tamano_id' => $tamano->id,
                    'masa_id' => $masa->id,
                    'cantidad' => 2,
                ],
                [
                    'tipo' => 'bebida',
                    'producto_id' => $bebida->id,
                    'cantidad' => 1,
                ],
            ]);
        });
    }
}
