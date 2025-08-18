<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
class PickupController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        // Defínelo en config/services.php → services['latina_api']['base_url']
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/'); // ej: http://127.0.0.1:8001/api
    }

    // GET /pickup → lista de sucursales para elegir
    public function index()
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->withErrors('Inicia sesión para continuar.');
        }

        try {
            $resp = Http::withToken($token)->get("{$this->apiBase}/sucursales");
            if (!$resp->successful()) {
                return back()->with('error', 'No se pudieron cargar las sucursales.');
            }
            $sucursales = $resp->json() ?? [];
        } catch (\Throwable $e) {
            return back()->with('error', 'Error de conexión con el API.');
        }

        return view('pedido.pickup', compact('sucursales'));
    }

    // POST /pickup/seleccionar → fija pickup en el carrito y redirige
    public function seleccionar(Request $r)
    {
        $token = Session::get('token');
        if (!$token) {
            return redirect()->route('login')->withErrors('Inicia sesión para continuar.');
        }

        $data = $r->validate([
            'sucursal_id' => ['required','integer'],
        ]);

        try {
            Http::withToken($token)->post("{$this->apiBase}/carrito/metodo-entrega", [
                'tipo'        => 'pickup',
                'sucursal_id' => (int) $data['sucursal_id'],
            ])->throw();

            // Cambia el destino si prefieres ir al carrito
            return redirect()->route('catalogo.index')->with('ok', 'Pickup seleccionado. ¡A ordenar!');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $json = $e->response?->json() ?? [];
            $msg = $json['error'] ?? 'No se pudo guardar tu selección.';
            return back()->with('error', $msg)->withInput();
        } catch (\Throwable $e) {
            return back()->with('error', 'Error de conexión con el API.');
        }
    }
}
