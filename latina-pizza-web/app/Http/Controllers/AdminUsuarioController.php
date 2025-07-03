<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
class AdminUsuarioController extends Controller
{
    public function index()
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get('http://127.0.0.1:8001/api/admin/usuarios');

        if ($response->successful()) {
            $usuarios = $response->json();
            return view('admin.usuarios.index', compact('usuarios'));
        } else {
            return back()->with('error', 'Error al obtener los usuarios');
        }
    }
    public function edit($id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->get("http://127.0.0.1:8001/api/admin/usuarios/{$id}");

        if ($response->successful()) {
            $usuario = $response->json();
            return view('admin.usuarios.edit', compact('usuario'));
        } else {
            return back()->with('error', 'Error al obtener el usuario');
        }
    }
    public function update(Request $request, $id)
    {
        $token = Session::get('token');

        $response = Http::withToken($token)->put("http://127.0.0.1:8001/api/admin/usuarios/{$id}", [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
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
            ->delete("http://127.0.0.1:8001/api/admin/usuarios/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado correctamente');
        } else {
            return redirect()->route('admin.usuarios.index')->with('error', 'Error al eliminar el usuario');
        }
    }
}
