<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use JsonException;

class SalonSessionController extends Controller
{
    public function show(int $session)
    {
        $token = Session::get('token');

        try {
            $sessionResponse = Http::connectTimeout(3)->timeout(8)->withToken($token)
                ->get($this->apiUrl("/salon/sesiones/{$session}"));
            $catalogResponse = Http::connectTimeout(3)->timeout(8)->withToken($token)
                ->get($this->apiUrl('/salon/catalogo'));
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if (! $sessionResponse->successful()) {
            return redirect()->route('salon.index')
                ->with('error', $this->responseMessage($sessionResponse, 'No se pudo cargar la mesa.'));
        }
        if (! $catalogResponse->successful()) {
            return redirect()->route('salon.index')
                ->with('error', $this->responseMessage($catalogResponse, 'No se pudo cargar el catálogo de salón.'));
        }

        return view('salon.session', [
            'sessionData' => $sessionResponse->json('data') ?? [],
            'catalog' => $catalogResponse->json('data') ?? [],
        ]);
    }

    public function storeRound(Request $request, int $session)
    {
        $validated = $request->validate([
            'items_json' => ['required', 'string'],
            'notas_cocina' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $items = json_decode($validated['items_json'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return back()->withInput()->with('error', 'La ronda no tiene un formato válido.');
        }

        if (! is_array($items) || count($items) < 1) {
            return back()->withInput()->with('error', 'Agregá al menos un producto antes de enviar la ronda.');
        }

        return $this->post(
            "/salon/sesiones/{$session}/rondas",
            ['items' => $items, 'notas_cocina' => $validated['notas_cocina'] ?? null],
            'Ronda enviada a cocina.'
        );
    }

    public function serve(int $pedido)
    {
        return $this->post("/salon/pedidos/{$pedido}/servir", [], 'Ronda marcada como servida.');
    }

    public function pay(Request $request, int $session)
    {
        $validated = $request->validate([
            'metodo_pago' => ['required', 'in:efectivo,datafono'],
            'payment_ref' => ['nullable', 'string', 'max:255'],
        ]);

        return $this->post("/salon/sesiones/{$session}/pagar", $validated, 'Cuenta pagada correctamente.');
    }

    public function cancel(int $pedido)
    {
        return $this->post("/salon/pedidos/{$pedido}/cancelar", [], 'Ronda cancelada correctamente.');
    }

    private function post(string $path, array $payload, string $fallback)
    {
        try {
            $response = Http::connectTimeout(3)->timeout(10)->withToken(Session::get('token'))
                ->post($this->apiUrl($path), $payload);
        } catch (ConnectionException $e) {
            return $this->apiUnavailable();
        } catch (\Throwable $e) {
            return $this->apiUnavailable();
        }

        if (! $response->successful()) {
            return back()->withInput()->with('error', $this->responseMessage($response, 'No se pudo completar la operación.'));
        }

        return back()->with('success', $response->json('message') ?: $fallback);
    }

    private function responseMessage(Response $response, string $fallback): string
    {
        $message = $response->json('message') ?: $response->json('error');
        if ($message) {
            return (string) $message;
        }

        $errors = collect($response->json('errors') ?? [])->flatten()->filter()->values();

        return (string) ($errors->first() ?: $fallback);
    }
}
