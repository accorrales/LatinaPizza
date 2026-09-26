<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TrackingController extends Controller
{
    public function customer(Request $request, int $id)
    {
        $response = $this->forward($request, 'GET', '/tracking/orders/'.$id);
        abort_unless($response->getStatusCode() === 200, $response->getStatusCode());

        return response()->view('tracking.customer', ['orderId' => $id])->header('Cache-Control', 'no-store, private');
    }

    public function location(Request $request, int $id)
    {
        return $this->forward($request, 'GET', '/tracking/orders/'.$id);
    }

    public function delivery()
    {
        return response()->view('tracking.delivery')->header('Cache-Control', 'no-store, private');
    }

    public function deliveryOrders(Request $request)
    {
        return $this->forward($request, 'GET', '/delivery/orders');
    }

    public function sendLocation(Request $request, int $id)
    {
        return $this->forward($request, 'POST', '/delivery/orders/'.$id.'/location',
            $request->only(['latitude', 'longitude', 'accuracy', 'recorded_at']));
    }

    private function forward(Request $request, string $method, string $path, array $data = [])
    {
        abort_unless($token = $request->session()->get('token'), 401);
        try {
            $response = Http::connectTimeout(3)->timeout(8)->withToken($token)->acceptJson()
                ->send($method, $this->apiUrl($path), $method === 'POST' ? ['json' => $data] : []);
        } catch (ConnectionException $exception) {
            return $this->apiUnavailable(true);
        }
        if ($response->serverError()) {
            return $this->apiUnavailable(true);
        }

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json')->header('Cache-Control', 'no-store, private');
    }

    public function index()
    {
        return response()->view('tracking.index')->header('Cache-Control', 'no-store, private');
    }

    public function orders(Request $request)
    {
        $token = $request->session()->get('token');
        abort_unless($token, 401);
        try {
            $response = Http::connectTimeout(3)->timeout(8)->withToken($token)->acceptJson()
                ->get($this->apiUrl('/tracking/orders'), $request->only(['search', 'estado', 'tipo', 'page']));
        } catch (ConnectionException $exception) {
            return $this->apiUnavailable(true);
        }

        if ($response->serverError()) {
            return $this->apiUnavailable(true);
        }

        return response($response->body(), $response->status())
            ->header('Content-Type', 'application/json')
            ->header('Cache-Control', 'no-store, private');
    }
}
