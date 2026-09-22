<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SucursalesExpressController extends Controller
{
    // Lista sucursales cercanas a la dirección
    public function index(Request $r)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login');
        }

        $r->validate(['direccion_usuario_id' => 'required|integer']);

        try {
            $resp = Http::connectTimeout(3)->timeout(5)->withToken($token)->get("{$this->apiBase}/sucursales/cercanas", [
                'direccion_usuario_id' => $r->direccion_usuario_id,
            ])->throwIfServerError()->throw();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        $direccion = $resp->json('direccion');
        $sucursales = $resp->json('sucursales') ?? [];

        return view('pedido.sucursales_express', compact('direccion', 'sucursales'));
    }

    // Fija Express con dirección + sucursal, y pasa al catálogo
    public function seleccionar(Request $r)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login');
        }

        $data = $r->validate([
            'direccion_usuario_id' => 'required|integer',
            'sucursal_id' => 'required|integer',
        ]);

        try {
            Http::connectTimeout(3)->timeout(5)->withToken($token)->post("{$this->apiBase}/carrito/metodo-entrega", [
                'tipo' => 'express',
                'direccion_usuario_id' => $data['direccion_usuario_id'],
                'sucursal_id' => $data['sucursal_id'],
            ])->throwIfServerError()->throw();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        session(['delivery.type' => 'express']); // 👈 marca la elección en sesión

        return redirect()->route('catalogo.index')->with('ok', 'Express seleccionado. ¡Listo para ordenar!');
    }
}
