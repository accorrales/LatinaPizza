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
}
