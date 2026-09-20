<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
class UserController extends Controller
{
    // Mostrar todos los usuarios (solo admins)
    public function index(Request $request)
    {
        $usuarios = User::with('sucursal:id,nombre')
            ->select('id', 'name', 'email', 'role', 'sucursal_id')
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 50), 1), 100));
        return response()->json($usuarios);
    }

    // Mostrar un usuario específico
    public function show($id)
    {
        $usuario = User::with('sucursal:id,nombre')->findOrFail($id);
        return response()->json($usuario);
    }

    // Actualizar un usuario
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $usuario->id,
            'role' => 'required|string|in:admin,cliente,cocina',
            'sucursal_id' => 'nullable|required_if:role,cocina|exists:sucursales,id',
        ]);

        if ($usuario->role === 'admin' && $validated['role'] !== 'admin'
            && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'No puede degradar al último administrador.'], 409);
        }

        $credentialsChanged = $usuario->email !== $validated['email'] || $usuario->role !== $validated['role'];
        $usuario->update($validated);
        if ($credentialsChanged) {
            $usuario->tokens()->delete();
        }

        return response()->json(['message' => 'Usuario actualizado correctamente']);
    }

    // Eliminar usuario
    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === request()->user()->id) {
            return response()->json(['message' => 'No puede eliminar su propia cuenta administrativa.'], 409);
        }
        if ($usuario->hasAnyRole('admin') && User::where('role', 'admin')->count() <= 1) {
            return response()->json(['message' => 'No puede eliminar el último administrador.'], 409);
        }
        if ($usuario->pedidos()->exists()) {
            return response()->json(['message' => 'El usuario tiene pedidos históricos y no puede eliminarse.'], 409);
        }

        $usuario->tokens()->delete();
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}
