<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PasswordRecoveryClient
{
    public function post(string $path, array $data): void
    {
        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(10)
                ->post(rtrim(config('app.api_url'), '/').'/api/'.$path, $data);
        } catch (ConnectionException) {
            throw ValidationException::withMessages(['email' => 'No pudimos conectar con el servicio. Intentá de nuevo en unos minutos.']);
        }

        if ($response->status() === 429) {
            throw ValidationException::withMessages(['email' => 'Alcanzaste el límite de intentos. Esperá 15 minutos antes de volver a intentarlo.']);
        }

        if ($response->status() === 422) {
            throw ValidationException::withMessages($response->json('errors') ?: ['code' => 'Revisá los datos e intentá de nuevo.']);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages(['email' => 'El servicio no está disponible en este momento. Intentá de nuevo en unos minutos.']);
        }
    }
}
