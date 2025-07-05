<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use App\Mail\EstadoPedidoMailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
class AdminPedidoController extends Controller
{
    public function index()
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get('http://127.0.0.1:8001/api/admin/pedidos');

        if ($response->successful()) {
            $pedidos = $response->json();
            return view('admin.pedidos.index', compact('pedidos'));
        } else {
            return back()->with('error', 'Error al obtener los pedidos');
        }
    }

    public function show($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get("http://127.0.0.1:8001/api/admin/pedidos/{$id}/ver");

        if ($response->successful()) {
            $pedido = $response->json();
            return view('admin.pedidos.show', compact('pedido'));
        } else {
            return back()->with('error', 'No se pudo cargar el pedido');
        }
    }
    public function cambiarEstado(Request $request, $id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->put("http://127.0.0.1:8001/api/admin/pedidos/{$id}/estado", [
            'estado' => $request->estado
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Estado actualizado correctamente');
        } else {
            return back()->with('error', 'Error al actualizar el estado');
        }
    }

    public function verHistorial($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get("http://127.0.0.1:8001/api/admin/pedidos/{$id}/historial");

        if ($response->successful()) {
            $historial = $response->json();
            return view('admin.pedidos.historial', [
                'historial' => $historial,
                'pedido_id' => $id
            ]);
        } else {
            return back()->with('error', 'No se pudo obtener el historial');
        }
    }
}
