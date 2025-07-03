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
        $token = Session::get('token'); // 🔐 Obtenemos el token guardado en sesión (de login API)

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para agregar productos al carrito');
        }

       $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/carrito/add', [
        'producto_id' => $request->input('producto_id'),
        'cantidad' => $request->input('cantidad', 1),
        ]);

        if ($response->successful()) {
            return redirect('/catalogo')->with('success', 'Producto agregado al carrito correctamente');
        } else {
            // 🧠 Mostrar el mensaje de error real
            return redirect('/catalogo')->with('error', 'Error al agregar producto: ' . $response->body());
        }
    }
    public function ver()
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para ver el carrito');
        }

        $response = Http::withToken($token)->get('http://127.0.0.1:8001/api/carrito');

        if ($response->successful()) {
            $carrito = $response->json(); // ⛽ Obtenemos los datos del carrito como array
            return view('carrito.index', compact('carrito'));
        } else {
            return redirect('/catalogo')->with('error', 'Error al cargar el carrito: ' . $response->body());
        }
    }
    public function eliminar($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->delete("http://127.0.0.1:8001/api/carrito/remove/$id");

        if ($response->successful()) {
            return redirect('/carrito')->with('success', 'Producto eliminado del carrito');
        } else {
            return redirect('/carrito')->with('error', 'Error al eliminar producto: ' . $response->body());
        }
    }
    public function actualizarCantidad(Request $request, $id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para modificar el carrito');
        }

        // Lógica de tipo de acción
        $accion = $request->input('accion');

        // Primero traemos el carrito para saber la cantidad actual
        $carritoResponse = Http::withToken($token)->get('http://127.0.0.1:8001/api/carrito');

        if (!$carritoResponse->successful()) {
            return back()->with('error', 'No se pudo obtener el carrito');
        }

        $carrito = $carritoResponse->json();
        $productoEnCarrito = collect($carrito['productos'])->firstWhere('id', $id);

        if (!$productoEnCarrito) {
            return back()->with('error', 'Producto no encontrado en el carrito');
        }

        $cantidadActual = $productoEnCarrito['pivot']['cantidad'];
        $nuevaCantidad = $accion === 'sumar' ? $cantidadActual + 1 : max(1, $cantidadActual - 1);

        // Actualizar cantidad (volvemos a usar el mismo endpoint del API de agregar)
        $response = Http::withToken($token)->post('http://127.0.0.1:8001/api/carrito/add', [
            'producto_id' => $id,
            'cantidad' => $nuevaCantidad
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Cantidad actualizada correctamente');
        } else {
            return back()->with('error', 'Error al actualizar cantidad: ' . $response->body());
        }
    }
}
