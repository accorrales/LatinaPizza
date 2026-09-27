<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class AdminUsuarioController extends Controller
{
    public function index(Request $request)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl('/admin/usuarios'), [
                'page' => max(1, $request->integer('page', 1)),
                'per_page' => 50,
            ])->throwIfServerError();
        } catch (ConnectionException $e) {
            $response = null;
        } catch (\Throwable $e) {
            $response = null;
        }

        if ($response?->successful()) {
            $payload = $response->json();
            $usuarios = $payload['data'] ?? $payload;
            $pagination = [
                'current_page' => (int) ($payload['current_page'] ?? 1),
                'last_page' => (int) ($payload['last_page'] ?? 1),
            ];

            return view('admin.usuarios.index', compact('usuarios', 'pagination'));
        }

        session()->flash('error', 'No se pudieron cargar los usuarios. Intenta de nuevo.');

        return view('admin.usuarios.index', [
            'usuarios' => [],
            'pagination' => ['current_page' => 1, 'last_page' => 1],
        ]);
    }

    public function edit($id)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->get($this->apiUrl("/admin/usuarios/{$id}"))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            $usuario = $response->json();
            try {
                $sucursales = Http::connectTimeout(3)->timeout(5)->get($this->apiUrl('/sucursales'))->throwIfServerError()->json() ?? [];
            } catch (ConnectionException $e) {
                return $this->apiUnavailable();
            } catch (\Throwable $e) {
                return $this->apiUnavailable();
            }

            return view('admin.usuarios.edit', compact('usuario', 'sucursales'));
        }

        return back()->with('error', 'Error al obtener el usuario');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::in(User::allowedRoles())],
            'sucursal_id' => [
                'nullable',
                Rule::requiredIf(fn () => User::roleRequiresBranch((string) $request->input('role'))),
                'integer',
            ],
        ]);
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)->put($this->apiUrl("/admin/usuarios/{$id}"), [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'sucursal_id' => $validated['sucursal_id'] ?? null,
            ])->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente');
        }

        return back()->with('error', 'Error al actualizar el usuario');
    }

    public function destroy($id)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(5)->withToken($token)
                ->delete($this->apiUrl("/admin/usuarios/{$id}"))->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if ($response->successful()) {
            return redirect()->route('admin.usuarios.index')->with('success', 'Usuario eliminado correctamente');
        }

        return redirect()->route('admin.usuarios.index')->with('error', 'Error al eliminar el usuario');
    }
}
