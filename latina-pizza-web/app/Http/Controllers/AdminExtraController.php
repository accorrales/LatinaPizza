<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminExtraController extends Controller
{
    public function index()
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get($this->apiUrl('/admin/extras-productos'));

        if ($response->successful()) {
            $extras = $response->json();

            return view('admin.extras.index', compact('extras'));
        }

        return back()->with('error', 'No se pudieron cargar los extras: '.$response->body());
    }

    public function create()
    {
        return view('admin.extras.create');
    }

    public function store(Request $request)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_pequena' => 'required|numeric|min:0',
            'precio_mediana' => 'required|numeric|min:0',
            'precio_grande' => 'required|numeric|min:0',
            'precio_extragrande' => 'required|numeric|min:0',
        ]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->apiUrl('/admin/extras-productos'), $validated);

        if ($response->successful()) {
            return redirect()->route('admin.extras.index')->with('success', 'Extra creado correctamente.');
        }

        return back()->with('error', 'Error al crear el extra: '.$response->body())->withInput();
    }

    public function edit($id)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->get($this->apiUrl("/admin/extras-productos/{$id}"));

        if ($response->successful()) {
            $extra = (object) $response->json();

            return view('admin.extras.edit', compact('extra'));
        }

        return redirect()->route('admin.extras.index')->with('error', 'No se pudo cargar el extra: '.$response->body());
    }

    public function update(Request $request, $id)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'precio_pequena' => 'required|numeric|min:0',
            'precio_mediana' => 'required|numeric|min:0',
            'precio_grande' => 'required|numeric|min:0',
            'precio_extragrande' => 'required|numeric|min:0',
        ]);

        $response = Http::withToken($token)
            ->acceptJson()
            ->put($this->apiUrl("/admin/extras-productos/{$id}"), $validated);

        if ($response->successful()) {
            return redirect()->route('admin.extras.index')->with('success', 'Extra actualizado correctamente.');
        }

        return back()->with('error', 'No se pudo actualizar el extra: '.$response->body())->withInput();
    }

    public function destroy($id)
    {
        $token = Session::get('token');
        if (! $token) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión');
        }

        $response = Http::withToken($token)->delete($this->apiUrl("/admin/extras-productos/{$id}"));

        if ($response->successful()) {
            return redirect()->route('admin.extras.index')->with('success', 'Extra eliminado correctamente.');
        }

        return back()->with('error', 'No se pudo eliminar el extra: '.$response->body());
    }
}
