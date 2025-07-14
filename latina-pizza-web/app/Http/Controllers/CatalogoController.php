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
        $sabores = [];
        $categorias = [];
        $promociones = [];

        try {
            // Rutas al backend
            $responseSabores = Http::get('http://127.0.0.1:8001/api/sabores-con-tamanos');
            $responseCategorias = Http::get('http://127.0.0.1:8001/api/categorias');
            $responsePromociones = Http::get('http://127.0.0.1:8001/api/promociones');

            if (
                $responseSabores->successful() &&
                $responseCategorias->successful() &&
                $responsePromociones->successful()
            ) {
                $sabores = $responseSabores->json();
                $categorias = $responseCategorias->json();
                $promociones = $responsePromociones->json()['data'];

                // Filtrar sabores por categoría si se seleccionó una
                if ($categoriaSeleccionada) {
                    $sabores = collect($sabores)
                        ->where('categoria_id', $categoriaSeleccionada)
                        ->values()
                        ->all();
                }
            } else {
                session()->flash('error', 'No se pudieron obtener los datos del menú.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error de conexión con la API.');
        }

        return view('catalogo.index', compact(
            'sabores',
            'categorias',
            'categoriaSeleccionada',
            'promociones'
        ));
    }
}

