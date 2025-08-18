<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PagosFrontController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        $this->apiBase = rtrim(config('services.latina_api.base_url'), '/'); // ej: http://127.0.0.1:8001/api
    }

    public function intent(Request $request)
    {
        $token = Session::get('token');
        if (!$token) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // llamamos a tu endpoint del API que creaste en el paso 2
        $resp = Http::withToken($token)->post("{$this->apiBase}/pagos/stripe/intent");

        return response()->json($resp->json(), $resp->status());
    }
}
