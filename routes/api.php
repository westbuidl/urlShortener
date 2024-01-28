<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileContoller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinessController;

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





Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/register', [UserController::class, 'create']);

Route::post('/individual', [UserController::class, 'individual']);//creating account for individuals
Route::post('/business', [BusinessController::class, 'business']);//creating account for business
Route::post('/userlogin', [UserController::class, 'userlogin']);//individual account login
Route::post('/businesslogin', [BusinessController::class, 'businesslogin']);//business account login
Route::post('/change_password', [ProfileContoller::class, 'change_password'])->middleware('auth:sanctum');//change password endpoint
Route::post('/update_profile', [ProfileContoller::class, 'update_profile'])->middleware('auth:sanctum');//profile update endpointupdate
Route::post('/account_setting', [ProfileContoller::class, 'account_setting'])->middleware('auth:sanctum');//profile update endpointupdate
Route::post('/billing_address', [ProfileContoller::class, 'billing_address'])->middleware('auth:sanctum');//profile update endpointupdate
Route::post('/user', [UserController::class, 'user'])->middleware('auth:sanctum');//api for access token protected routes
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');//api for logout