<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpressController extends Controller
{
    public function index()
    {
        return view('pedido.express');
    }
}

