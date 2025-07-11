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

        try {
            // ✅ CAMBIO IMPORTANTE: ruta correcta del backend
            $responseSabores = Http::get('http://127.0.0.1:8001/api/sabores-con-tamanos');
            $responseCategorias = Http::get('http://127.0.0.1:8001/api/categorias');

            if ($responseSabores->successful() && $responseCategorias->successful()) {
                $sabores = $responseSabores->json();
                $categorias = $responseCategorias->json();

                // ✅ Filtra por categoría si hay una seleccionada
                if ($categoriaSeleccionada) {
                    $sabores = collect($sabores)
                        ->where('categoria_id', $categoriaSeleccionada)
                        ->values()
                        ->all();
                }
            } else {
                session()->flash('error', 'No se pudieron obtener los sabores o categorías.');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error de conexión con la API.');
        }

        return view('catalogo.index', compact(
            'sabores',
            'categorias',
            'categoriaSeleccionada'
        ));
    }
}
