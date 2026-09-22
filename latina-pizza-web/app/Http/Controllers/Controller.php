<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected string $apiBase;

    public function __construct()
    {
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/');
    }

    protected function apiUrl(string $path = ''): string
    {
        return $this->apiBase.'/'.ltrim($path, '/');
    }

    protected function apiUnavailable(bool $json = false)
    {
        $message = 'No se pudo conectar con el servidor, intenta de nuevo.';

        if ($json || request()->expectsJson() || request()->ajax()) {
            return response()->json(['error' => $message, 'message' => $message], 503);
        }

        // A failed GET must not redirect back to the same unavailable page.
        return (request()->isMethod('GET') ? redirect()->route('catalogo.index') : back())
            ->with('error', $message);
    }
}
