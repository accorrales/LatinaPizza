<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $categoriaSeleccionada = $request->query('categoria_id');
        $productos = [];
        $categorias = [];
        $agrupadosPorTamanio = [
            'Pequeñas' => [],
            'Medianas' => [],
            'Grandes' => [],
            'Extra Grandes' => [],
            'Otros' => []
        ];

        try {
            $responseProductos = Http::get('http://127.0.0.1:8001/api/productos');
            $responseCategorias = Http::get('http://127.0.0.1:8001/api/categorias');

            if ($responseProductos->successful() && $responseCategorias->successful()) {
                $productos = $responseProductos->json();
                $categorias = $responseCategorias->json();

                // Filtra si hay categoría seleccionada
                if ($categoriaSeleccionada) {
                    $productos = collect($productos)
                        ->where('categoria_id', $categoriaSeleccionada)
                        ->values()
                        ->all();
                }

                // Verifica si la categoría es Pizza o si no se seleccionó ninguna
                $categoriaPizza = collect($categorias)->firstWhere('nombre', 'Pizza');

                if (
                    ($categoriaPizza && $categoriaSeleccionada == $categoriaPizza['id']) ||
                    is_null($categoriaSeleccionada)
                ) {
                    // Agrupa por tamaño
                    foreach ($productos as $producto) {
                        $nombre = strtolower($producto['nombre']);
                        if (str_contains($nombre, 'extragrande') || str_contains($nombre, 'extra grande')) {
                            $agrupadosPorTamanio['Extra Grandes'][] = $producto;
                        } elseif (str_contains($nombre, 'grande')) {
                            $agrupadosPorTamanio['Grandes'][] = $producto;
                        } elseif (str_contains($nombre, 'mediana')) {
                            $agrupadosPorTamanio['Medianas'][] = $producto;
                        } elseif (str_contains($nombre, 'pequeña')) {
                            $agrupadosPorTamanio['Pequeñas'][] = $producto;
                        } else {
                            $agrupadosPorTamanio['Otros'][] = $producto;
                        }
                    }
                } else {
                    // Si no es Pizza, vacía agrupación
                    $agrupadosPorTamanio = null;
                }
            } else {
                session()->flash('error', 'No se pudieron obtener los productos o categorías.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error de conexión con la API.');
        }

        return view('catalogo.index', compact(
            'productos',
            'categorias',
            'categoriaSeleccionada',
            'agrupadosPorTamanio'
        ));
    }
}
