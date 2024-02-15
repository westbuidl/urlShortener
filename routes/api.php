<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileContoller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\SellerProfileContoller;

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

Route::post('/individual', [UserController::class, 'individual'])->name('individual');//creating account for individuals
Route::post('/business', [BusinessController::class, 'business'])->name('business');//creating account for business
Route::post('/userlogin', [UserController::class, 'userlogin'])->name('userlogin');//individual account login
Route::post('/businesslogin', [BusinessController::class, 'businesslogin'])->name('businesslogin');//business account login
Route::post('/change_password', [ProfileContoller::class, 'change_password'])->middleware('auth:sanctum')->name('change_password');//change password endpoint
Route::post('/seller_change_password', [SellerProfileContoller::class, 'seller_change_password'])->middleware('auth:sanctum')->name('seller_change_password');//change password endpoint for sellers
Route::post('/update_profile', [ProfileContoller::class, 'update_profile'])->middleware('auth:sanctum')->name('update_profile');//profile image update endpoint
Route::post('/seller_update_profile', [SellerProfileContoller::class, 'seller_update_profile'])->middleware('auth:sanctum')->name('seller_update_profile');// seller profile image update endpoint 
Route::post('/account_setting', [ProfileContoller::class, 'account_setting'])->middleware('auth:sanctum')->name('account_setting');//profile update endpointupdate for 
Route::post('/billing_address', [ProfileContoller::class, 'billing_address'])->middleware('auth:sanctum')->name('billing_address');//profile update endpointupdate for 
Route::post('/seller_account_setting', [SellerProfileContoller::class, 'seller_account_setting'])->middleware('auth:sanctum')->name('seller_account_setting');//profile update endpoint for sellers
Route::post('/addproduct', [ProductController::class, 'addproduct'])->middleware('auth:sanctum')->name('addproduct');//profile update endpoint for sellers
Route::get('/viewproduct/{product_id}', [ProductController::class, 'viewproduct'])->middleware('auth:sanctum')->name('viewproduct');//view products
Route::get('/searchproducts/{name}', [ProductController::class, 'searchproducts'])->middleware('auth:sanctum')->name('searchproducts');//search products
Route::delete('/deleteproduct/{product_id}', [ProductController::class, 'deleteproduct'])->middleware('auth:sanctum');//profile update endpoint for sellers
Route::post('/user', [UserController::class, 'user'])->middleware('auth:sanctum');//api for access token protected routes
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');//api for logout
Route::post('/verifymail', [UserController::class, 'verifymail'])->name('verifymail');//send verification email
Route::post('/verifyMailBusiness', [BusinessController::class, 'verifyMailBusiness'])->name('verifyMailBusiness');//send verification email for business account setup
//Auth::routes(['verify' => true]);