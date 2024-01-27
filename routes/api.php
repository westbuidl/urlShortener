<?php

use App\Http\Controllers\BusinessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('/products', [ProductController::class, 'index']);
//Route::get('/products', function(){
 //return 'products';
//});


Route::get('/register', [UserController::class, 'create']);

Route::post('/individual', [UserController::class, 'individual']);

Route::post('/business', [BusinessController::class, 'business']);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
