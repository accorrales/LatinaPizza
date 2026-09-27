<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = User::with('sucursal:id,nombre')
            ->select('id', 'name', 'email', 'role', 'sucursal_id')
            ->orderBy('name')
            ->paginate(min(max($request->integer('per_page', 50), 1), 100));

        return response()->json($usuarios);
    }

    public function show($id)
    {
        $usuario = User::with('sucursal:id,nombre')->findOrFail($id);

        return response()->json($usuario);
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role' => ['required', 'string', Rule::in(User::allowedRoles())],
            'sucursal_id' => [
                'nullable',
                Rule::requiredIf(fn () => User::roleRequiresBranch((string) $request->input('role'))),
                'integer',
                'exists:sucursales,id',
            ],
        ]);

        if ($usuario->role === User::ROLE_ADMIN && $validated['role'] !== User::ROLE_ADMIN
            && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return response()->json(['message' => 'No puede degradar al último administrador.'], 409);
        }

        $emailChanged = $usuario->email !== $validated['email'];
        $roleChanged = $usuario->role !== $validated['role'];
        $branchChanged = (int) ($usuario->sucursal_id ?? 0) !== (int) ($validated['sucursal_id'] ?? 0);

        $usuario->fill($validated);
        if ($emailChanged) {
            // Un correo nuevo nunca debe heredar la verificación del correo anterior.
            $usuario->forceFill(['email_verified_at' => null]);
        }
        $usuario->save();

        if ($emailChanged || $roleChanged || $branchChanged) {
            // Revoca tokens persistentes cuando cambia identidad o alcance operativo.
            $usuario->tokens()->delete();
        }

        return response()->json([
            'message' => $emailChanged
                ? 'Usuario actualizado. El nuevo correo debe verificarse antes de volver a usar la cuenta.'
                : 'Usuario actualizado correctamente',
        ]);
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->id === request()->user()->id) {
            return response()->json(['message' => 'No puede eliminar su propia cuenta administrativa.'], 409);
        }
        if ($usuario->hasAnyRole(User::ROLE_ADMIN) && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return response()->json(['message' => 'No puede eliminar el último administrador.'], 409);
        }
        if ($usuario->pedidos()->exists()) {
            return response()->json(['message' => 'El usuario tiene pedidos históricos y no puede eliminarse.'], 409);
        }
        if ($usuario->pedidosCreados()->exists() || $usuario->mesaSesiones()->exists()) {
            return response()->json([
                'message' => 'El colaborador tiene historial operativo y no puede eliminarse. Cambie su rol o retire sus permisos operativos en lugar de eliminarlo.',
            ], 409);
        }

        $usuario->tokens()->delete();
        $usuario->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}
