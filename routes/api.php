<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileContoller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
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

//Begin Api routes for Individual account functions(Buyers and Sellers)
Route::get('/register', [UserController::class, 'create']);
Route::post('/individual', [UserController::class, 'individual'])->name('individual');//creating account for individuals
Route::post('/userlogin', [UserController::class, 'userlogin'])->name('userlogin');//individual account login
Route::post('/change_password', [ProfileContoller::class, 'change_password'])->middleware('auth:sanctum')->name('change_password');//change password endpoint
Route::post('/update_profile', [ProfileContoller::class, 'update_profile'])->middleware('auth:sanctum')->name('update_profile');//profile image update endpoint
Route::delete('/delete_userprofilepicture/{id}', [ProfileContoller::class, 'delete_userprofilepicture'])->middleware('auth:sanctum')->name('delete_userprofilepicture');//profile image update endpoint
Route::post('/account_setting', [ProfileContoller::class, 'account_setting'])->middleware('auth:sanctum')->name('account_setting');//profile update endpointupdate for 
Route::post('/billing_address', [ProfileContoller::class, 'billing_address'])->middleware('auth:sanctum')->name('billing_address');//profile update endpointupdate for 
Route::post('/user', [UserController::class, 'user'])->middleware('auth:sanctum');//api for access token protected routes
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');//api for logout//});
Route::post('/verifymail', [UserController::class, 'verifymail'])->name('verifymail');//send verification email
Route::post('/resendverificationcode', [UserController::class, 'resendverificationcode'])->name('resendcode');//resend verification code
Route::post('/resetpassword', [UserController::class, 'resetpassword'])->name('resetpassword');//resend verification code
Route::get('/getUserProfile/{id}', [UserController::class, 'getUserProfile'])->middleware(('auth:sanctum')) ->name('getUserProfile');//api to get user profile
//End Api routs for individual account





//---Begin Api routes for Business account functions --//
Route::post('/business', [BusinessController::class, 'business'])->name('business');//creating account for business
Route::post('/businesslogin', [BusinessController::class, 'businesslogin'])->name('businesslogin');//business account login
Route::post('/seller_change_password', [SellerProfileContoller::class, 'seller_change_password'])->middleware('auth:sanctum')->name('seller_change_password');//change password endpoint for sellers
Route::delete('/delete_businessprofilepicture/{id}', [SellerProfileContoller::class, 'delete_businessprofilepicture'])->middleware('auth:sanctum')->name('delete_businessprofilepicture');//profile image update endpoint
Route::post('/seller_update_profile', [SellerProfileContoller::class, 'seller_update_profile'])->middleware('auth:sanctum')->name('seller_update_profile');// seller profile image update endpoint 
Route::post('/seller_account_setting', [SellerProfileContoller::class, 'seller_account_setting'])->middleware('auth:sanctum')->name('seller_account_setting');//profile update endpoint for sellers
Route::post('/businessresetpassword', [BusinessController::class, 'businessresetpassword'])->name('businessresetpassword');//resend verification code
Route::post('/verifyMailBusiness', [BusinessController::class, 'verifyMailBusiness'])->name('verifyMailBusiness');//send verification email for business account setup
//---Begin Api routes for Business account functions --//



//--Begin of Product api --//
Route::post('/addToCart', [ProductController::class, 'addToCart'])->middleware('auth:sanctum')->name('addToCart');// add to cart
Route::post('/addproduct', [ProductController::class, 'addproduct'])->middleware('auth:sanctum')->name('addproduct');//profile update endpoint for sellers
Route::get('/viewproduct/{product_id}', [ProductController::class, 'viewproduct'])->middleware('auth:sanctum')->name('viewproduct');//view products
Route::get('/allProducts', [ProductController::class, 'allProducts'])->name('allProducts');//view products
Route::post('/editproduct/{product_id}', [ProductController::class, 'editproduct'])->middleware('auth:sanctum')->name('editproduct');//view products
Route::post('/restockproduct/{product_id}', [ProductController::class, 'restockproduct'])->middleware('auth:sanctum')->name('restockproduct');//restock product
Route::post('/productstate/{product_id}', [ProductController::class, 'productstate'])->middleware('auth:sanctum')->name('productstate');//make the product active/inactive
Route::get('/searchproducts/{name}', [ProductController::class, 'searchproducts'])->middleware('auth:sanctum')->name('searchproducts');//search products
Route::delete('/deleteproduct/{product_id}', [ProductController::class, 'deleteproduct'])->middleware('auth:sanctum');//profile update endpoint for sellers
Route::post('/toggleProductState/{product_id}', [ProductController::class, 'toggleProductState'])->middleware('auth:sanctum')->name('toggleProductState');//toggle product state
//--End of Product api--//


//--Begin of Admi api --//

Route::get('/categoryDetails/{id}', [CategoryController::class, 'categoryDetails'])->name('categoryDetails');//get category details
Route::post('/addCategory', [CategoryController::class, 'addCategory'])->name('addCategory');//add categories
Route::delete('/deleteCategory/{id}', [CategoryController::class, 'deleteCategory'])->name('deleteCategory');//delete categories
Route::get('/viewAllcategory', [CategoryController::class, 'viewAllcategory'])->name('viewAllcategory');//view all categories
Route::get('/viewCategory/{categoryID}', [CategoryController::class, 'viewCategory'])->name('viewCategory');//view all categories

//--End of Admin api --//

//Add to cart
Route::post('/storeCart', [CartController::class, 'storeCart'])->name('showCart');//adding to cart api
Route::get('/showCart', [CartController::class, 'showCart'])->name('showCart');//adding to cart api
Route::get('/addToCart/{id}', [ProductController::class, 'addToCart'])->name('addToCart');//adding to cart api
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');//adding to cart api
Route::post('/deleteCart', [CartController::class, 'deleteCart'])->name('deleteCart');//adding to cart api