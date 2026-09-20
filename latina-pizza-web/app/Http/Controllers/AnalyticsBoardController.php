<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnalyticsBoardController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()?->role === 'admin', 403); // doble check server-side

        return view('analytics.ventas');
    }

    public function proxy(Request $request, string $report)
    {
        abort_unless(in_array($report, ['sales/daily', 'sales/weekly', 'sales/monthly', 'products/top'], true), 404);
        $token = $request->session()->get('token');
        abort_unless($token, 401);

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(10)
            ->get($this->apiUrl('/analytics/'.$report), $request->query());

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
    }
}
