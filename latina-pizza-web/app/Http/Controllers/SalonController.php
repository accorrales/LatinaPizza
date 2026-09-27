<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class SalonController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, [User::ROLE_ADMIN, User::ROLE_GERENTE, User::ROLE_MESERO, User::ROLE_CAJERO], true), 403);

        $token = Session::get('token');

        try {
            $branchesResponse = Http::connectTimeout(3)->timeout(5)->get($this->apiUrl('/sucursales'))->throwIfServerError();
            $sucursales = collect($branchesResponse->json() ?? []);
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        $selectedBranchId = $user->role === User::ROLE_ADMIN
            ? ($request->integer('sucursal_id') ?: (int) ($user->sucursal_id ?: ($sucursales->first()['id'] ?? 0)))
            : (int) $user->sucursal_id;

        if (! $selectedBranchId) {
            return view('salon.index', [
                'mesas' => [],
                'meseros' => [],
                'sucursales' => $sucursales,
                'selectedBranchId' => null,
                'selectedBranch' => null,
            ])->with('error', 'No hay una sucursal disponible para mostrar el salón.');
        }

        try {
            $response = Http::connectTimeout(3)->timeout(8)->withToken($token)
                ->get($this->apiUrl('/salon/mesas'), ['sucursal_id' => $selectedBranchId]);
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if (! $response->successful()) {
            return back()->with('error', $this->responseMessage($response, 'No se pudo cargar el salón.'));
        }

        $payload = $response->json();
        $selectedBranch = $sucursales->firstWhere('id', $selectedBranchId);

        return view('salon.index', [
            'mesas' => $payload['data'] ?? [],
            'meseros' => $payload['meta']['meseros'] ?? [],
            'sucursales' => $sucursales,
            'selectedBranchId' => $selectedBranchId,
            'selectedBranch' => $selectedBranch,
        ]);
    }

    public function storeMesa(Request $request)
    {
        $validated = $request->validate([
            'sucursal_id' => ['nullable', 'integer'],
            'numero' => ['required', 'string', 'max:40'],
            'nombre' => ['nullable', 'string', 'max:120'],
            'zona' => ['nullable', 'string', 'max:120'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        return $this->postToSalon('/salon/mesas', $validated, 'Mesa creada correctamente.');
    }

    public function openMesa(Request $request, int $mesa)
    {
        $validated = $request->validate([
            'personas' => ['required', 'integer', 'min:1', 'max:50'],
            'mesero_user_id' => ['nullable', 'integer'],
            'notas' => ['nullable', 'string', 'max:1000'],
            'sucursal_id' => ['nullable', 'integer'],
        ]);

        $branchId = $validated['sucursal_id'] ?? null;
        unset($validated['sucursal_id']);

        return $this->postToSalon("/salon/mesas/{$mesa}/abrir", $validated, 'Mesa abierta correctamente.', $branchId);
    }

    public function closeSession(Request $request, int $session)
    {
        $validated = $request->validate([
            'sucursal_id' => ['nullable', 'integer'],
        ]);

        return $this->postToSalon(
            "/salon/sesiones/{$session}/cerrar",
            [],
            'Mesa cerrada correctamente.',
            $validated['sucursal_id'] ?? null
        );
    }

    private function postToSalon(string $path, array $payload, string $successMessage, ?int $branchId = null)
    {
        $token = Session::get('token');

        try {
            $response = Http::connectTimeout(3)->timeout(8)->withToken($token)
                ->post($this->apiUrl($path), $payload);
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if (! $response->successful()) {
            return back()->withInput()->with('error', $this->responseMessage($response, 'No se pudo completar la operación.'));
        }

        $target = route('salon.index', array_filter(['sucursal_id' => $branchId]));

        return redirect($target)->with('success', $response->json('message') ?: $successMessage);
    }

    private function responseMessage(Response $response, string $fallback): string
    {
        return (string) ($response->json('message') ?: $response->json('error') ?: $fallback);
    }
}
