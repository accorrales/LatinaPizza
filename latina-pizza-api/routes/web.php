<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\API\LocaleController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['es','en'])
    ->name('lang.switch');

require __DIR__.'/auth.php';
