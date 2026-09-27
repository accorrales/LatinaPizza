<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\MesaSesion;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalonController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $mesas = Mesa::query()
            ->where('sucursal_id', $branchId)
            ->where('activo', true)
            ->with(['sesionActiva.mesero:id,name'])
            ->orderByRaw("coalesce(zona, '')")
            ->orderBy('numero')
            ->get()
            ->map(fn (Mesa $mesa) => $this->tablePayload($mesa));

        $meseros = User::query()
            ->where('role', User::ROLE_MESERO)
            ->where('sucursal_id', $branchId)
            ->whereNotNull('email_verified_at')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'data' => $mesas,
            'meta' => [
                'sucursal_id' => $branchId,
                'meseros' => $meseros,
            ],
        ]);
    }

    public function storeMesa(Request $request)
    {
        $branchId = $this->resolveBranchId($request, true);

        $validated = $request->validate([
            'numero' => [
                'required',
                'string',
                'max:40',
                Rule::unique('mesas', 'numero')->where(fn ($query) => $query->where('sucursal_id', $branchId)),
            ],
            'nombre' => ['nullable', 'string', 'max:120'],
            'zona' => ['nullable', 'string', 'max:120'],
            'capacidad' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $mesa = Mesa::create($validated + [
            'sucursal_id' => $branchId,
            'estado' => Mesa::DISPONIBLE,
            'activo' => true,
        ]);

        return response()->json([
            'message' => 'Mesa creada correctamente.',
            'data' => $this->tablePayload($mesa),
        ], 201);
    }

    public function updateMesa(Request $request, Mesa $mesa)
    {
        $this->authorizeBranch($request, $mesa->sucursal_id);

        $validated = $request->validate([
            'numero' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                Rule::unique('mesas', 'numero')
                    ->where(fn ($query) => $query->where('sucursal_id', $mesa->sucursal_id))
                    ->ignore($mesa->id),
            ],
            'nombre' => ['sometimes', 'nullable', 'string', 'max:120'],
            'zona' => ['sometimes', 'nullable', 'string', 'max:120'],
            'capacidad' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        if (($validated['activo'] ?? true) === false && $mesa->sesionActiva()->exists()) {
            return response()->json(['message' => 'No se puede desactivar una mesa ocupada.'], 409);
        }

        $mesa->update($validated);

        return response()->json([
            'message' => 'Mesa actualizada correctamente.',
            'data' => $this->tablePayload($mesa->fresh()->load('sesionActiva.mesero:id,name')),
        ]);
    }

    public function openMesa(Request $request, Mesa $mesa)
    {
        $this->authorizeBranch($request, $mesa->sucursal_id);

        $validated = $request->validate([
            'personas' => ['required', 'integer', 'min:1', 'max:50'],
            'mesero_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($request, $mesa, $validated) {
            $lockedMesa = Mesa::query()->lockForUpdate()->findOrFail($mesa->id);
            $this->authorizeBranch($request, $lockedMesa->sucursal_id);

            if (! $lockedMesa->activo) {
                return response()->json(['message' => 'La mesa está fuera de servicio.'], 409);
            }

            if ($lockedMesa->estado !== Mesa::DISPONIBLE || $lockedMesa->sesionActiva()->exists()) {
                return response()->json(['message' => 'La mesa ya está ocupada o no está disponible.'], 409);
            }

            $waiterId = $this->resolveWaiterId($request, $lockedMesa->sucursal_id, $validated['mesero_user_id'] ?? null);

            $session = MesaSesion::create([
                'mesa_id' => $lockedMesa->id,
                'sucursal_id' => $lockedMesa->sucursal_id,
                'mesero_user_id' => $waiterId,
                'estado' => MesaSesion::ABIERTA,
                'personas' => $validated['personas'],
                'opened_at' => now(),
                'notas' => $validated['notas'] ?? null,
            ]);

            $lockedMesa->update(['estado' => Mesa::OCUPADA]);

            return response()->json([
                'message' => 'Mesa abierta correctamente.',
                'data' => $this->tablePayload($lockedMesa->fresh()->load('sesionActiva.mesero:id,name')),
                'session_id' => $session->id,
            ], 201);
        });
    }

    public function closeSession(Request $request, MesaSesion $session)
    {
        $this->authorizeBranch($request, $session->sucursal_id);

        return DB::transaction(function () use ($request, $session) {
            $lockedSession = MesaSesion::query()->lockForUpdate()->findOrFail($session->id);
            $this->authorizeBranch($request, $lockedSession->sucursal_id);

            if ($request->user()->role === User::ROLE_MESERO
                && (int) $lockedSession->mesero_user_id !== (int) $request->user()->id) {
                abort(403, 'No puede cerrar la mesa de otro mesero.');
            }

            if ($lockedSession->estado !== MesaSesion::ABIERTA) {
                return response()->json(['message' => 'La sesión de mesa ya está cerrada.'], 409);
            }

            $orders = Pedido::query()
                ->where('mesa_sesion_id', $lockedSession->id)
                ->where('canal_venta', Pedido::CANAL_SALON)
                ->where('estado', '!=', 'cancelado')
                ->lockForUpdate()
                ->get();

            if ($orders->contains(fn (Pedido $order) => $order->kitchen_status !== 'entregado')) {
                return response()->json([
                    'message' => 'La mesa todavía tiene rondas pendientes de servir.',
                ], 409);
            }

            if ($orders->contains(fn (Pedido $order) => $order->payment_status !== 'paid')) {
                return response()->json([
                    'message' => 'La cuenta todavía tiene rondas pendientes de pago.',
                ], 409);
            }

            $lockedSession->cerrar();

            $mesa = Mesa::query()->lockForUpdate()->findOrFail($lockedSession->mesa_id);
            $mesa->update(['estado' => Mesa::DISPONIBLE]);

            return response()->json([
                'message' => 'Mesa cerrada y liberada correctamente.',
                'data' => $this->tablePayload($mesa->fresh()->load('sesionActiva.mesero:id,name')),
            ]);
        });
    }

    private function resolveBranchId(Request $request, bool $fromBody = false): int
    {
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->role !== User::ROLE_ADMIN) {
            abort_unless($user->sucursal_id, 403, 'El colaborador no tiene una sucursal asignada.');

            return (int) $user->sucursal_id;
        }

        $requested = $fromBody ? $request->input('sucursal_id') : $request->query('sucursal_id');
        $branchId = $requested ?: $user->sucursal_id;

        if (! $branchId) {
            abort(422, 'Seleccione una sucursal.');
        }

        abort_unless(DB::table('sucursales')->where('id', $branchId)->exists(), 422, 'Sucursal inválida.');

        return (int) $branchId;
    }

    private function authorizeBranch(Request $request, int $branchId): void
    {
        $user = $request->user();
        abort_unless($user, 401);

        if ($user->role !== User::ROLE_ADMIN) {
            abort_unless($user->sucursal_id && (int) $user->sucursal_id === $branchId, 403, 'No autorizado para esta sucursal.');
        }
    }

    private function resolveWaiterId(Request $request, int $branchId, ?int $requestedWaiterId): int
    {
        $user = $request->user();

        if ($user->role === User::ROLE_MESERO) {
            return (int) $user->id;
        }

        abort_unless($requestedWaiterId, 422, 'Seleccione un mesero responsable de la mesa.');

        $waiter = User::query()
            ->whereKey($requestedWaiterId)
            ->where('role', User::ROLE_MESERO)
            ->where('sucursal_id', $branchId)
            ->whereNotNull('email_verified_at')
            ->first();

        abort_unless($waiter, 422, 'Seleccione un mesero verificado de la sucursal.');

        return (int) $waiter->id;
    }

    private function tablePayload(Mesa $mesa): array
    {
        $session = $mesa->sesionActiva;

        return [
            'id' => $mesa->id,
            'sucursal_id' => $mesa->sucursal_id,
            'numero' => $mesa->numero,
            'nombre' => $mesa->nombre,
            'zona' => $mesa->zona,
            'capacidad' => (int) $mesa->capacidad,
            'estado' => $mesa->estado,
            'activo' => (bool) $mesa->activo,
            'session' => $session ? [
                'id' => $session->id,
                'estado' => $session->estado,
                'personas' => (int) $session->personas,
                'opened_at' => $session->opened_at?->toIso8601String(),
                'notas' => $session->notas,
                'mesero' => $session->mesero ? [
                    'id' => $session->mesero->id,
                    'name' => $session->mesero->name,
                ] : null,
            ] : null,
        ];
    }
}
