<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Extra;
use App\Models\Masa;
use App\Models\Producto;
use App\Models\Sabor;
use App\Models\Sucursal;
use App\Models\Tamano;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $pizza = Categoria::firstOrCreate(['nombre' => 'Pizzas'], ['descripcion' => 'Pizzas personalizables']);
        $bebidas = Categoria::firstOrCreate(['nombre' => 'Bebidas'], ['descripcion' => 'Bebidas y refrescos']);

        $sizes = collect([
            ['nombre' => 'Pequeña', 'precio_base' => 4500],
            ['nombre' => 'Mediana', 'precio_base' => 6500],
            ['nombre' => 'Grande', 'precio_base' => 8500],
            ['nombre' => 'Extragrande', 'precio_base' => 10500],
        ])->mapWithKeys(function ($data) {
            $size = Tamano::updateOrCreate(['nombre' => $data['nombre']], $data);
            return [$data['nombre'] => $size];
        });

        foreach ([
            ['tipo' => 'Tradicional', 'precio_extra' => 0],
            ['tipo' => 'Delgada', 'precio_extra' => 0],
            ['tipo' => 'Borde de queso', 'precio_extra' => 1500],
        ] as $data) {
            Masa::updateOrCreate(['tipo' => $data['tipo']], $data);
        }

        foreach ([
            ['nombre' => 'Queso extra', 'precio_pequena' => 500, 'precio_mediana' => 700, 'precio_grande' => 900, 'precio_extragrande' => 1100],
            ['nombre' => 'Pepperoni extra', 'precio_pequena' => 600, 'precio_mediana' => 800, 'precio_grande' => 1000, 'precio_extragrande' => 1200],
        ] as $data) {
            Extra::updateOrCreate(['nombre' => $data['nombre']], $data);
        }

        foreach ([
            ['nombre' => 'Pepperoni', 'descripcion' => 'Queso mozzarella y pepperoni.'],
            ['nombre' => 'Hawaiana', 'descripcion' => 'Jamón, piña y queso mozzarella.'],
            ['nombre' => 'Suprema', 'descripcion' => 'Carnes y vegetales seleccionados.'],
        ] as $flavorData) {
            $flavor = Sabor::firstOrCreate(['nombre' => $flavorData['nombre']], $flavorData);
            foreach ($sizes as $size) {
                Producto::updateOrCreate([
                    'sabor_id' => $flavor->id,
                    'tamano_id' => $size->id,
                ], [
                    'nombre' => "{$flavor->nombre} {$size->nombre}",
                    'descripcion' => $flavor->descripcion,
                    'precio' => $size->precio_base,
                    'categoria_id' => $pizza->id,
                    'estado' => true,
                ]);
            }
        }

        foreach ([
            ['nombre' => 'Coca-Cola', 'precio' => 1800],
            ['nombre' => 'Coca-Cola Zero', 'precio' => 1800],
            ['nombre' => 'Fresca', 'precio' => 1800],
        ] as $drink) {
            Producto::updateOrCreate([
                'nombre' => $drink['nombre'],
                'categoria_id' => $bebidas->id,
            ], [
                'descripcion' => 'Bebida',
                'precio' => $drink['precio'],
                'estado' => true,
            ]);
        }

        Sucursal::firstOrCreate(['nombre' => 'Sucursal Demo Naranjo'], [
            'direccion' => 'Naranjo, Alajuela',
            'latitud' => 10.0989,
            'longitud' => -84.3783,
        ]);
    }
}
