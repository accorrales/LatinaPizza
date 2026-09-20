<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
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
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('sabores')->timeout(5)->get($this->apiUrl('/productos-sabores-tamanos')),
                $pool->as('categorias')->timeout(5)->get($this->apiUrl('/categorias')),
                $pool->as('promociones')->timeout(5)->get($this->apiUrl('/promociones')),
            ]);
            $responseSabores = $responses['sabores'];
            $responseCategorias = $responses['categorias'];
            $responsePromociones = $responses['promociones'];

            if (
                $responseSabores instanceof Response &&
                $responseCategorias instanceof Response &&
                $responsePromociones instanceof Response &&
                $responseSabores->successful() &&
                $responseCategorias->successful() &&
                $responsePromociones->successful()
            ) {
                $sabores = $responseSabores->json();
                $categoriasPayload = $responseCategorias->json();
                $categorias = $categoriasPayload['data'] ?? $categoriasPayload;
                $promociones = $responsePromociones->json()['data'] ?? [];

                // 🔍 Filtrar por categoría si viene en la query
                if ($categoriaSeleccionada) {
                    $sabores = collect($sabores)
                        ->where('categoria_id', $categoriaSeleccionada)
                        ->values()
                        ->all();
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
