<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AdminUsuarioController extends Controller
{
    public function index(Request $request)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get($this->apiUrl('/admin/usuarios'), [
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => 50,
        ]);

        if ($response->successful()) {
            $payload = $response->json();
            $usuarios = $payload['data'] ?? $payload;
            $pagination = [
                'current_page' => (int) ($payload['current_page'] ?? 1),
                'last_page' => (int) ($payload['last_page'] ?? 1),
            ];

            return view('admin.usuarios.index', compact('usuarios', 'pagination'));
        } else {
            return back()->with('error', 'Error al obtener los usuarios');
        }
    }

    public function edit($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get($this->apiUrl("/admin/usuarios/{$id}"));

        if ($response->successful()) {
            $usuario = $response->json();
            $sucursales = Http::get($this->apiUrl('/sucursales'))->json() ?? [];

            return view('admin.usuarios.edit', compact('usuario', 'sucursales'));
        } else {
            return back()->with('error', 'Error al obtener el usuario');
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:admin,cliente,cocina'],
            'sucursal_id' => ['nullable', 'integer'],
        ]);
        $token = Session::get('token');

        $response = Http::withToken($token)->put($this->apiUrl("/admin/usuarios/{$id}"), [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'sucursal_id' => $validated['sucursal_id'] ?? null,
        ]);

        if ($response->successful()) {
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente');
        } else {
            return back()->with('error', 'Error al actualizar el usuario');
        }
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)
            ->delete($this->apiUrl("/admin/usuarios/{$id}"));

        if ($response->successful()) {
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado correctamente');
        } else {
            return redirect()->route('admin.usuarios.index')->with('error', 'Error al eliminar el usuario');
        }
    }
}
