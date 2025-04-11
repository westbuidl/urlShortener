<?php

use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;

Route::post('/encode', [UrlController::class, 'encode']);
Route::post('/decode', [UrlController::class, 'decode']);