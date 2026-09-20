<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ResenaController extends Controller
{
    public function verResenas($id)
    {
        $resenas = [];
        $puedeCalificar = false;

        $token = Session::get('token');

        $resenaResponse = Http::get($this->apiUrl("/resenas/$id"));
        if ($resenaResponse->successful()) {
            $resenas = $resenaResponse->json();
        }

        if ($token) {
            $verificacion = Http::withToken($token)->get($this->apiUrl("/resenas/verificar-compra/$id"));

            if ($verificacion->successful()) {
                $puedeCalificar = $verificacion->json()['comprado'];
            }
        }

        return view('resenas.index', [
            'resenas' => $resenas,
            'puedeCalificar' => $puedeCalificar,
            'saborId' => $id, // esto era lo que faltaba
        ]);

    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->post($this->apiUrl('/resenas'), [
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

        $response = Http::withToken($token)->put($this->apiUrl("/resenas/$id"), [
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

        $response = Http::withToken($token)->delete($this->apiUrl("/resenas/$id"));

        if ($response->successful()) {
            return back()->with('success', '¡Reseña eliminada correctamente!');
        } else {
            $mensaje = $response->json('error') ?? 'Error al eliminar reseña.';

            return back()->with('error', $mensaje);
        }
    }
}
