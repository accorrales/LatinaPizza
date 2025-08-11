<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $categoriaSeleccionada = $request->query('categoria_id');
        $sabores = [];
        $categorias = [];
        $promociones = [];

        try {
            // 🌐 Llamadas a la API pública
            $responseSabores     = Http::get('http://127.0.0.1:8001/api/productos-sabores-tamanos');
            $responseCategorias  = Http::get('http://127.0.0.1:8001/api/categorias');
            $responsePromociones = Http::get('http://127.0.0.1:8001/api/promociones');

            if (
                $responseSabores->successful() &&
                $responseCategorias->successful() &&
                $responsePromociones->successful()
            ) {
                $sabores     = $responseSabores->json();
                $categorias  = $responseCategorias->json()['data'] ?? [];
                $promociones = $responsePromociones->json()['data'] ?? [];

                // 🔍 Filtrar por categoría si viene en la query
                if ($categoriaSeleccionada) {
                    $sabores = collect($sabores)
                        ->where('categoria_id', $categoriaSeleccionada)
                        ->values()
                        ->all();
                }

                // ⭐ Agregar promedio de reseñas por sabor
                foreach ($sabores as $index => $sabor) {
                    try {
                        $resPromedio = Http::get("http://127.0.0.1:8001/api/resenas-promedio/{$sabor['sabor_id']}");
                        $data = $resPromedio->json();

                        $sabores[$index]['promedio']       = $data['promedio'] ?? 0;
                        $sabores[$index]['total_resenas'] = $data['total'] ?? 0;
                    } catch (\Exception $e) {
                        $sabores[$index]['promedio'] = 0;
                        $sabores[$index]['total_resenas'] = 0;
                    }
                }
            } else {
                session()->flash('error', 'No se pudieron obtener los datos del catálogo.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error de conexión con el servidor.');
        }

        return view('home', compact(
            'sabores',
            'categorias',
            'categoriaSeleccionada',
            'promociones'
        ));
    }
}
