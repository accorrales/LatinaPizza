<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminPromocionController extends Controller
{
    private $apiBase = 'http://127.0.0.1:8001/api';

    public function index()
    {
        $token = Session::get('token');
        $response = Http::withToken($token)->get("{$this->apiBase}/promociones");

        if ($response->successful()) {
            $promociones = $response->json()['data'];
            return view('admin.promociones.index', compact('promociones'));
        }

        return back()->with('error', 'No se pudieron cargar las promociones.');
    }

    public function create()
    {
        $token = Session::get('token');

        // Ahora sí, correcto
        $tamanos = Http::withToken($token)
            ->get("{$this->apiBase}/admin/tamanos")
            ->json()['data'] ?? [];

        return view('admin.promociones.create', compact('tamanos'));
    }

    public function store(Request $request)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|string',
            'incluye_bebida' => 'nullable|boolean', // ✅ validamos checkbox

            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer',
        ]);

        $data = $validated;
        $data['incluye_bebida'] = $request->has('incluye_bebida'); // ✅ checkbox como booleano (true o false)

        $response = Http::withToken($token)
            ->post("http://127.0.0.1:8001/api/admin/promociones", $data); // ✅ cambia la URL si tenés otra

        if ($response->successful()) {
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción creada correctamente');
        }

        return back()->with('error', 'Error al guardar la promoción.')->withInput();
    }


    public function destroy($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->delete("{$this->apiBase}/promociones/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.promociones.index')->with('success', 'Promoción eliminada correctamente');
        }

        return back()->with('error', 'No se pudo eliminar la promoción');
    }


    public function edit($id)
    {
        $token = Session::get('token');

        if (!$token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        // ✅ Obtener la promoción a editar
        $promocion = Http::withToken($token)->get("{$this->apiBase}/promociones/{$id}")->json()['data'] ?? null;

        // ✅ Obtener todos los datos necesarios para los select
        $tamanos = Http::withToken($token)->get("{$this->apiBase}/admin/tamanos")->json()['data'] ?? [];
        $sabores = Http::withToken($token)->get("{$this->apiBase}/admin/sabores")->json()['data'] ?? [];
        $masas   = Http::withToken($token)->get("{$this->apiBase}/admin/masas")->json()['data'] ?? [];

        // ✅ Retornar la vista con todos los datos
        return view('admin.promociones.edit', compact('promocion', 'tamanos', 'sabores', 'masas'));
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio_total' => 'required|numeric|min:0',
            'precio_sugerido' => 'nullable|numeric|min:0',
            'imagen' => 'nullable|string',
            'incluye_bebida' => 'nullable|boolean',
            'componentes' => 'required|array|min:1',
            'componentes.*.tipo' => 'required|in:pizza,bebida',
            'componentes.*.cantidad' => 'required|integer|min:1',
            'componentes.*.tamano_id' => 'nullable|integer',
        ]);

        $data = $validated;
        $data['incluye_bebida'] = $request->has('incluye_bebida');

        // 👇 Enviar como POST con override _method=PUT
        $response = Http::withToken($token)->put("{$this->apiBase}/promociones/{$id}", $validated);

        // 🔍 Mostrar respuesta si falla
        if (!$response->successful()) {
            dd([
                'status' => $response->status(),
                'body' => $response->body(),
                'validated' => $validated,
            ]);
        }
        return redirect()->route('admin.promociones.index')->with('success', 'Promoción actualizada correctamente');
    }

}
