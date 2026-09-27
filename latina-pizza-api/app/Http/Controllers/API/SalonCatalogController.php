<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Extra;
use App\Models\Masa;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Sabor;

class SalonCatalogController extends Controller
{
    public function index()
    {
        $products = Producto::query()
            ->where('estado', true)
            ->with(['categoria:id,nombre', 'sabor:id,nombre', 'tamano:id,nombre,precio_base'])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Producto $product) => [
                'id' => $product->id,
                'nombre' => $product->nombre,
                'imagen' => $product->imagen,
                'categoria' => $product->categoria?->nombre,
                'sabor' => $product->sabor?->nombre,
                'tamano' => $product->tamano?->nombre,
                'is_pizza' => (bool) ($product->sabor_id && $product->tamano_id),
                'precio' => (float) ($product->tamano?->precio_base ?? $product->precio ?? 0),
            ]);

        $masses = Masa::query()
            ->orderBy('tipo')
            ->get(['id', 'tipo', 'precio_extra']);

        $extras = Extra::query()
            ->orderBy('nombre')
            ->get([
                'id', 'nombre', 'precio_pequena', 'precio_mediana',
                'precio_grande', 'precio_extragrande',
            ]);

        $flavors = Sabor::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $drinks = Producto::query()
            ->where('estado', true)
            ->whereHas('categoria', function ($query) {
                $query->where(function ($category) {
                    $category->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%bebid%'])
                        ->orWhereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%refresc%']);
                });
            })
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $promotions = Promocion::query()
            ->with('componentes.tamano:id,nombre')
            ->orderBy('nombre')
            ->get()
            ->map(fn (Promocion $promotion) => [
                'id' => $promotion->id,
                'nombre' => $promotion->nombre,
                'descripcion' => $promotion->descripcion,
                'imagen' => $promotion->imagen,
                'precio' => (float) $promotion->precio_total,
                'componentes' => $promotion->componentes->map(fn ($component) => [
                    'tipo' => $component->tipo,
                    'cantidad' => max(1, (int) $component->cantidad),
                    'tamano_id' => $component->tamano_id,
                    'tamano' => $component->tamano?->nombre,
                ])->values(),
            ]);

        return response()->json([
            'data' => [
                'products' => $products,
                'promotions' => $promotions,
                'masses' => $masses,
                'extras' => $extras,
                'flavors' => $flavors,
                'drinks' => $drinks,
            ],
        ]);
    }
}
