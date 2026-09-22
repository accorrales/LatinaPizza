<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Throwable;

class AdminPedidoController extends Controller
{
    public function index(Request $request)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl('/admin/pedidos'), [
                'page' => max(1, $request->integer('page', 1)),
                'per_page' => 25,
            ])->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $pedidos = $response->json('data', []);
            $pagination = [
                'current_page' => (int) $response->json('current_page', 1),
                'last_page' => (int) $response->json('last_page', 1),
            ];

            return view('admin.pedidos.index', compact('pedidos', 'pagination'));
        } else {
            return back()->with('error', 'Error al obtener los pedidos');
        }
    }

    public function show($id)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl("/admin/pedidos/{$id}/ver"))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

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

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->put($this->apiUrl("/admin/pedidos/{$id}/estado"), [
                'estado' => $request->estado,
            ])->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return back()->with('success', 'Estado actualizado correctamente');
        } else {
            return back()->with('error', $response->json('message', 'Error al actualizar el estado'));
        }
    }

    public function verHistorial($id)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl("/admin/pedidos/{$id}/historial"))->throwIfServerError();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $historial = $response->json();

            return view('admin.pedidos.historial', [
                'historial' => $historial,
                'pedido_id' => $id,
            ]);
        } else {
            return back()->with('error', 'No se pudo obtener el historial');
        }
    }

    public function refund($id)
    {
        $token = Session::get('token');
        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->post($this->apiUrl("/admin/pedidos/{$id}/refund"))->throwIfServerError();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'No se pudo conectar con el servicio de pagos.');
        }

        if ($response->successful()) {
            return back()->with('success', $response->json('message', 'Reembolso procesado.'));
        }

        return back()->with('error', $response->json('message', 'No se pudo procesar el reembolso.'));
    }
}
