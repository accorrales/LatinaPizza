<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\DetallePedido;
use App\Models\Resena;
class ResenaController extends Controller
{
    public function ver($saborId)
    {
        $productoResponse = Http::get("http://127.0.0.1:8001/api/sabor/$saborId");
        $resenasResponse = Http::get("http://127.0.0.1:8001/api/resenas/$saborId");

        if ($productoResponse->successful() && $resenasResponse->successful()) {
            $producto = $productoResponse->json();
            $resenas = $resenasResponse->json();

            return view('resenas.ver', compact('producto', 'resenas'));
        } else {
            return back()->with('error', 'No se pudieron obtener los datos.');
        }
    }
    
    public function verResenas($id)
    {
        $resenas = [];
        $puedeCalificar = false;

        $token = Session::get('token');

        $resenaResponse = Http::get("http://127.0.0.1:8001/api/resenas/$id");
        if ($resenaResponse->successful()) {
            $resenas = $resenaResponse->json();
        }

        if ($token) {
            $verificacion = Http::withToken($token)->get("http://127.0.0.1:8001/api/resenas/verificar-compra/$id");

            if ($verificacion->successful()) {
                $puedeCalificar = $verificacion->json()['comprado'];
            }
        }

        return view('resenas.index', [
            'resenas' => $resenas,
            'puedeCalificar' => $puedeCalificar,
            'saborId' => $id // esto era lo que faltaba
        ]);

    }
    public function store(Request $request)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/resenas', [
            'sabor_id' => $request->sabor_id,
            'calificacion' => $request->calificacion,
            'comentario' => $request->comentario,
        ]);

        if ($response->successful()) {
            return back()->with('success', '¡Reseña registrada correctamente!');
        } else {
            $mensaje = $response->json('error') ?? 'Error al guardar reseña.';
            return back()->with('error', $mensaje);
        }
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->put("http://127.0.0.1:8001/api/resenas/$id", [
            'calificacion' => $request->calificacion,
            'comentario' => $request->comentario,
        ]);

        if ($response->successful()) {
            return back()->with('success', '¡Reseña actualizada correctamente!');
        } else {
            $mensaje = $response->json('error') ?? 'Error al actualizar reseña.';
            return back()->with('error', $mensaje);
        }
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->delete("http://127.0.0.1:8001/api/resenas/$id");

        if ($response->successful()) {
            return back()->with('success', '¡Reseña eliminada correctamente!');
        } else {
            $mensaje = $response->json('error') ?? 'Error al eliminar reseña.';
            return back()->with('error', $mensaje);
        }
    }
    public function crear($saborId)
    {
        return view('resenas.crear', compact('saborId'));
    }
}

