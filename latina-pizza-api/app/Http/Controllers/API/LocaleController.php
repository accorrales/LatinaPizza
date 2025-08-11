<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;

class LocaleController extends Controller
{
    public function switch($locale)
    {
        if (!in_array($locale, ['es','en'])) {
            $locale = config('app.fallback_locale');
        }
        Session::put('locale', $locale);
        return back();
    }
}