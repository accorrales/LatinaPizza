<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class KitchenBoardController extends Controller
{
    public function index()
    {
        // Valida rol mínimo en la vista (y oculta el acceso si no es admin/cocina)
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['admin', 'cocina']), 403);

        return view('kitchen.index');
    }

    public function proxy(Request $request, string $path = '')
    {
        abort_unless(preg_match('#^(orders(?:/.*)?)$#', $path) === 1, 404);
        $token = $request->session()->get('token');
        abort_unless($token, 401);

        try {
            $response = Http::connectTimeout(3)->withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->send($request->method(), $this->apiUrl('/kitchen/'.$path), [
                    'query' => $request->query(),
                    'json' => $request->all(),
                ])->throwIfServerError();
        } catch (ConnectionException $e) {
            return $this->apiUnavailable(true);
        } catch (\Throwable $e) {
            return $this->apiUnavailable(true);
        }

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json');
    }
}
