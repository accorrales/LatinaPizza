<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Promocion;
use App\Models\PromocionComponente;

class PromocionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Crea la promoción base
        $promo = Promocion::create([
            'nombre' => 'Promo 2 Pizzas + Refresco',
            'descripcion' => 'Disfruta dos pizzas medianas a elegir y un refresco incluido.',
            'precio_base' => 11500,
            'incluye_bebida' => true,
            'imagen' => 'https://i.postimg.cc/SQVJD4SB/Screenshot-11-7-2025-5100-www-instagram-com.jpg',
        ]);


        // Agrega dos componentes tipo pizza
        for ($i = 0; $i < 2; $i++) {
            PromocionComponente::create([
            'promocion_id' => 1,
            'tipo' => 'pizza',
            'tamano_id' => 2,
            'masa_id' => 1,
            'sabor_id' => 1,
            'cantidad' => 2,
        ]);
        }

        // Agrega un componente tipo bebida
        PromocionComponente::create([
            'promocion_id' => $promo->id,
            'tipo' => 'bebida',
            'producto_id' => 10 // ID de un refresco real (ajústalo a uno que tengas)
        ]);
    }
}
