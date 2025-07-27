<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Resena;
class CatalogoController extends Controller
{
    public function index(Request $request)
    {
        $categoriaSeleccionada = $request->query('categoria_id');
        $sabores = [];
        $categorias = [];
        $promociones = [];

        try {
            // 🌐 Consultas a la API
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

                // 🔻 Filtrar por categoría si se seleccionó una
                if ($categoriaSeleccionada) {
                    $sabores = collect($sabores)
                        ->where('categoria_id', $categoriaSeleccionada)
                        ->values()
                        ->all();
                }

                // ⭐ Agregar promedio de reseñas y total a cada sabor
                foreach ($sabores as $index => $sabor) {
                    try {
                        $response = Http::get("http://127.0.0.1:8001/api/resenas-promedio/{$sabor['sabor_id']}");

                        if ($response->successful()) {
                            $data = $response->json();
                            $sabores[$index]['promedio'] = $data['promedio'];
                            $sabores[$index]['total_resenas'] = $data['total'];
                        } else {
                            $sabores[$index]['promedio'] = 0;
                            $sabores[$index]['total_resenas'] = 0;
                        }
                    } catch (\Exception $e) {
                        $sabores[$index]['promedio'] = 0;
                        $sabores[$index]['total_resenas'] = 0;
                    }
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

