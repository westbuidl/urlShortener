<?php

use App\Http\Controllers\UrlController;
use App\Http\Controllers\RedirectController;
use Illuminate\Support\Facades\Route;


Route::post('/encode', [UrlController::class, 'encode']);
Route::post('/decode', [UrlController::class, 'decode']);


Route::get('/{shortCode}', [RedirectController::class, 'redirect'])->where('shortCode', '[A-Za-z0-9]+');