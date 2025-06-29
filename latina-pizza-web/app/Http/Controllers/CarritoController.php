<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
class CarritoController extends Controller
{
    public function agregar(Request $request)
    {
        // Verificamos si hay token en sesión
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para agregar productos al carrito');
        }

        // Enviar petición al backend con el token
        $response = Http::withToken($token)
            ->post('http://127.0.0.1:8001/api/carrito/agregar', [
                'producto_id' => $request->input('producto_id'),
                'cantidad' => $request->input('cantidad', 1),
            ]);

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Producto agregado al carrito correctamente');
        } else {
            return redirect()->back()->with('error', 'No se pudo agregar el producto al carrito');
        }
    }
}
