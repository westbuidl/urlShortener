<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\ProfileContoller;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaystackController;
use App\Http\Controllers\BuyerProfileController;
use App\Http\Controllers\CompanyBuyerController;
use App\Http\Controllers\CompanySellerController;
use App\Http\Controllers\SellerProfileController;

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

//Begin Api routes for buyer account functions(Buyers and Sellers)
Route::get('/register', [BuyerController::class, 'create']);
Route::post('/signup', [BuyerController::class, 'signup'])->name('signup');//creating account for buyer
Route::post('/loginBuyer', [BuyerController::class, 'loginBuyer'])->name('loginBuyer');//buyer account login
Route::post('/changeBuyerPassword', [BuyerProfileController::class, 'changeBuyerPassword'])->middleware('auth:sanctum')->name('changeBuyerPassword');//change password endpoint
Route::post('/updateBuyerProfilePicture', [BuyerProfileController::class, 'updateBuyerProfilePicture'])->middleware('auth:sanctum')->name('updateBuyerProfilePicture');//profile image update endpoint
Route::delete('/deleteBuyerProfilePicture/{buyerId}', [BuyerProfileController::class, 'deleteBuyerProfilePicture'])->middleware('auth:sanctum')->name('deleteBuyerProfilePicture');//profile image update endpoint
Route::post('/updateBuyerAccountDetails', [BuyerProfileController::class, 'updateBuyerAccountDetails'])->middleware('auth:sanctum')->name('updateBuyerAccountDetails');//profile update endpointupdate for 
Route::post('/updateBuyerBillingAddress', [BuyerProfileController::class, 'updateBuyerBillingAddress'])->middleware('auth:sanctum')->name('updateBuyerBillingAddress');//profile update endpointupdate for 
Route::post('/buyer', [BuyerController::class, 'user'])->middleware('auth:sanctum');//api for access token protected routes
Route::post('/logout', [BuyerController::class, 'logout'])->middleware('auth:sanctum');//api for logout//});
Route::post('/verifyBuyerEmail', [BuyerController::class, 'verifyBuyerEmail'])->name('verifyBuyerEmail');//send verification email
Route::post('/resendBuyerEmailAuth/{email}', [BuyerController::class, 'resendBuyerEmailAuth'])->name('resendBuyerEmailAuth');//resend verification code
Route::post('/buyerPasswordReset', [BuyerController::class, 'buyerPasswordReset'])->name('buyerPasswordReset');//reset buyer password
Route::get('/getBuyerProfile/{buyerId}', [BuyerController::class, 'getBuyerProfile'])->middleware(('auth:sanctum')) ->name('getBuyerProfile');//api to get user profile
//End Api routes for Buyer account





//Begin Api routes for seller account functions
Route::get('/register', [SellerController::class, 'create']);
Route::post('/sellerSignup', [SellerController::class, 'sellerSignup'])->name('sellerSignup');//creating account for buyer
Route::post('/loginSeller', [SellerController::class, 'loginSeller'])->name('loginSeller');//buyer account login
Route::post('/changeSellerPassword', [SellerProfileController::class, 'changeSellerPassword'])->middleware('auth:sanctum')->name('changeSellerPassword');//change password endpoint
Route::post('/updateSellerProfilePicture', [SellerProfileController::class, 'updateSellerProfilePicture'])->middleware('auth:sanctum')->name('updateSellerProfilePicture');//profile image update endpoint
Route::delete('/deleteSellerProfilePicture/{seller_Id}', [SellerProfileController::class, 'deleteSellerProfilePicture'])->middleware('auth:sanctum')->name('deleteSellerProfilePicture');//profile image update endpoint
Route::post('/updateSellerAccountDetails', [SellerProfileController::class, 'updateSellerAccountDetails'])->middleware('auth:sanctum')->name('updateSellerAccountDetails');//profile update endpointupdate for 
Route::post('/updateSellerAddress', [SellerProfileController::class, 'updateSellerAddress'])->middleware('auth:sanctum')->name('updateSellerAddress');//profile update endpointupdate for 
Route::post('/buyer', [BuyerController::class, 'user'])->middleware('auth:sanctum');//api for access token protected routes
Route::post('/logout', [SellerController::class, 'logout'])->middleware('auth:sanctum');//api for logout//});
Route::post('/verifySellerEmail', [SellerController::class, 'verifySellerEmail'])->name('verifySellerEmail');//send verification email
Route::post('/resendSellerEmailAuth/{email}', [SellerController::class, 'resendSellerEmailAuth'])->name('resendSellerEmailAuth');//resend verification code
Route::post('/sellerPasswordReset', [SellerController::class, 'sellerPasswordReset'])->name('sellerPasswordReset');//reset buyer password
Route::get('/getSellerProfile/{sellerId}', [SellerProfileController::class, 'getSellerProfile'])->middleware(('auth:sanctum')) ->name('getSellerProfile');//api to get user profile
Route::post('/addBankAccount', [SellerProfileController::class, 'addBankAccount'])->middleware(('auth:sanctum')) ->name('addBankAccount');//api to get user profile
Route::post('/verifyBankAccount', [SellerProfileController::class, 'verifyBankAccount'])->middleware(('auth:sanctum')) ->name('verifyBankAccount');//api to get user profile
Route::get('/getBankAccountDetails', [SellerProfileController::class, 'getBankAccountDetails'])->middleware(('auth:sanctum')) ->name('getBankAccountDetails');//api to get user profile
Route::get('/recentSales', [SellerProfileController::class, 'recentSales'])->middleware(('auth:sanctum')) ->name('recentSales');//recent sales
Route::get('/totalProfit', [SellerProfileController::class, 'totalProfit'])->middleware(('auth:sanctum')) ->name('totalProfit');//get Total profit
Route::get('/totalSales', [SellerProfileController::class, 'totalSales'])->middleware(('auth:sanctum')) ->name('totalSales');//get Total Sales
Route::get('/totalOrder', [SellerProfileController::class, 'totalOrder'])->middleware(('auth:sanctum')) ->name('totalOrder');//get Total Sales
Route::get('/totalReturn', [SellerProfileController::class, 'totalReturn'])->middleware(('auth:sanctum')) ->name('totalReturn');//get Total Sales


//End Api routes for Buyer account



//---Begin Api routes for CompanyBuyer --//
Route::post('/companyBuyerSignup', [CompanyBuyerController::class, 'companyBuyerSignup'])->name('companyBuyerSignup');//creating account for business
Route::post('/companyBuyerLogin', [CompanyBuyerController::class, 'companyBuyerLogin'])->name('companyBuyerLogin');//business account login
Route::post('/companyBuyerChangePassword', [CompanyBuyerController::class, 'companyBuyerChangePassword'])->middleware('auth:sanctum')->name('companyBuyerChangePassword');//change password endpoint for sellers
Route::delete('/deleteCompanyBuyerProfilePicture/{companyBuyerId}', [CompanyBuyerController::class, 'deleteCompanyBuyerProfilePicture'])->middleware('auth:sanctum')->name('deleteCompanyBuyerProfilePicture');//profile image update endpoint
Route::post('/updateCompanyBuyerProfilePicture', [CompanyBuyerController::class, 'updateCompanyBuyerProfilePicture'])->middleware('auth:sanctum')->name('updateCompanyBuyerProfilePicture');// seller profile image update endpoint 
Route::post('/updateCompanyBuyerAccountDetails', [CompanyBuyerController::class, 'updateCompanyBuyerAccountDetails'])->middleware('auth:sanctum')->name('updateCompanyBuyerAccountDetails');//profile update endpoint for sellers
Route::post('/companyBuyerResetPassword', [CompanyBuyerController::class, 'companyBuyerResetPassword'])->name('companyBuyerResetPassword');//resend verification code
Route::post('/companyBuyerVerifyMail', [CompanyBuyerController::class, 'companyBuyerVerifyMail'])->name('companyBuyerVerifyMail');//send verification email for business account setup
Route::post('/companyBuyerChangePassword', [CompanyBuyerController::class, 'companyBuyerChangePassword'])->name('companyBuyerChangePassword');//resend verification code
Route::post('/resendCompanyBuyerEmailAuth/{email}', [CompanyBuyerController::class, 'resendCompanyBuyerEmailAuth'])->name('resendCompanyBuyerEmailAuth');//resend verification code
Route::get('/getCompanyBuyerProfile/{companyBuyerId}', [CompanyBuyerController::class, 'getCompanyBuyerProfile'])->name('getCompanyBuyerProfile');//resend verification code

//---Begin Api routes for CompanySeller --//
Route::post('/companySellerSignup', [CompanySellerController::class, 'companySellerSignup'])->name('companySellerSignup');//creating account for business
Route::post('/companySellerLogin', [CompanySellerController::class, 'companySellerLogin'])->name('companySellerLogin');//business account login
Route::post('/companySellerChangePassword', [CompanySellerController::class, 'companySellerChangePassword'])->middleware('auth:sanctum')->name('companySellerChangePassword');//change password endpoint for sellers
Route::delete('/deleteCompanySellerProfilePicture/{companySellerId}', [CompanySellerController::class, 'deleteCompanySellerProfilePicture'])->middleware('auth:sanctum')->name('deleteCompanySellerProfilePicture');//profile image update endpoint
Route::post('/companySellerUpdateProfile', [CompanySellerController::class, 'companySellerUpdateProfile'])->middleware('auth:sanctum')->name('companySellerUpdateProfile');// seller profile image update endpoint 
Route::post('/companySellerAccountSetting', [CompanySellerController::class, 'companySellerAccountSetting'])->middleware('auth:sanctum')->name('companySellerAccountSetting');//profile update endpoint for sellers
Route::post('/companySellerResetPassword', [CompanySellerController::class, 'companySellerResetPassword'])->name('companySellerResetPassword');//resend verification code
Route::post('/companySellerVerifyMail', [CompanySellerController::class, 'companySellerVerifyMail'])->name('companySellerVerifyMail');//send verification email for business account setup
Route::post('/resendCompanySellerEmailAuth/{email}', [CompanySellerController::class, 'resendCompanySellerEmailAuth'])->name('resendCompanySellerEmailAuth');//resend verification code
Route::get('/getCompanySellerProfile/{companySellerId}', [CompanySellerController::class, 'getCompanySellerProfile'])->name('getCompanySellerProfile');//resend verification code
//---Begin Api routes for Business account functions --//


//---Begin Api routes for CompanySeller --//
Route::post('/business', [BusinessController::class, 'business'])->name('business');//creating account for business
Route::post('/businesslogin', [BusinessController::class, 'businesslogin'])->name('businesslogin');//business account login
Route::post('/seller_change_password', [SellerProfileController::class, 'seller_change_password'])->middleware('auth:sanctum')->name('seller_change_password');//change password endpoint for sellers
Route::delete('/delete_businessprofilepicture/{id}', [SellerProfileController::class, 'delete_businessprofilepicture'])->middleware('auth:sanctum')->name('delete_businessprofilepicture');//profile image update endpoint
Route::post('/seller_update_profile', [SellerProfileController::class, 'seller_update_profile'])->middleware('auth:sanctum')->name('seller_update_profile');// seller profile image update endpoint 
Route::post('/seller_account_setting', [SellerProfileController::class, 'seller_account_setting'])->middleware('auth:sanctum')->name('seller_account_setting');//profile update endpoint for sellers
Route::post('/businessresetpassword', [BusinessController::class, 'businessresetpassword'])->name('businessresetpassword');//resend verification code
Route::post('/verifyMailBusiness', [BusinessController::class, 'verifyMailBusiness'])->name('verifyMailBusiness');//send verification email for business account setup
//---Begin Api routes for Business account functions --//




//Add to cart
Route::post('/storeCart', [CartController::class, 'storeCart'])->name('showCart');//adding to cart api
Route::get('/viewCart/{buyerId}', [CartController::class, 'viewCart'])->middleware('auth:sanctum')->name('viewCart');//adding to cart api
Route::post('/addToCart/{productId}', [CartController::class, 'addToCart'])->middleware('auth:sanctum')->name('addToCart');//adding to cart api

Route::delete('/deleteCartItem/{cartId}', [CartController::class, 'deleteCartItem'])->middleware('auth:sanctum')->name('deleteCartItem');//adding to cart api
Route::post('/updateCartItem/{cartId}', [CartController::class, 'updatecartItem'])->middleware('auth:sanctum')->name('updatecartItem');//adding to cart api
//End adding product to cart endpoints



//--Begin of Product api --//
//Route::post('/addToCart', [ProductController::class, 'addToCart'])->middleware('auth:sanctum')->name('addToCart');// add to cart
Route::post('/addProduct', [ProductController::class, 'addProduct'])->middleware('auth:sanctum')->name('addProduct');//profile update endpoint for sellers
Route::get('/viewProduct/{productId}', [ProductController::class, 'viewProduct'])->middleware('auth:sanctum')->name('viewProduct');//view products
Route::get('/allProducts', [ProductController::class, 'allProducts'])->name('allProducts');//view products
Route::get('/productDetails/{productId}', [ProductController::class, 'productDetails'])->name('productDetails');//productDetails
Route::post('/editProduct/{productId}', [ProductController::class, 'editProduct'])->middleware('auth:sanctum')->name('editProduct');//view products
Route::post('/restockProduct/{productId}', [ProductController::class, 'restockproduct'])->middleware('auth:sanctum')->name('restockproduct');//restock product
Route::post('/productstate/{productId}', [ProductController::class, 'productstate'])->middleware('auth:sanctum')->name('productstate');//make the product active/inactive
Route::get('/searchProducts/{name}', [ProductController::class, 'searchProducts'])->name('searchProducts');//search products
Route::delete('/deleteProduct/{productId}', [ProductController::class, 'deleteProduct'])->middleware('auth:sanctum');//profile update endpoint for sellers
Route::post('/toggleProductState/{productId}', [ProductController::class, 'toggleProductState'])->middleware('auth:sanctum')->name('toggleProductState');//toggle product state
Route::get('/hotDeals', [ProductController::class, 'hotDeals'])->name('hotDeals');//toggle product state
Route::get('/popularProducts', [ProductController::class, 'popularProducts'])->name('popularProducts');//toggle product state
//Route::post('/checkout/{buyerId}', [ProductController::class, 'checkout'])->middleware('auth:sanctum')->name('checkout');// add to cart
//--End of Product api-/-//


//--Begin of Admi api --//

Route::get('/categoryDetails/{categoryID}', [CategoryController::class, 'categoryDetails'])->name('categoryDetails');//get category details
Route::post('/addCategory', [CategoryController::class, 'addCategory'])->name('addCategory');//add categories
Route::delete('/deleteCategory/{categoryID}', [CategoryController::class, 'deleteCategory'])->name('deleteCategory');//delete categories
Route::get('/viewAllcategory', [CategoryController::class, 'viewAllcategory'])->name('viewAllcategory');//view all categories
Route::get('/viewCategory/{categoryID}', [CategoryController::class, 'viewCategory'])->name('viewCategory');//view all categories
Route::get('/popularCategories', [CategoryController::class, 'popularCategories'])->name('popularCategories');//view all categories
Route::post('/editCategory/{categoryID}', [CategoryController::class, 'editCategory'])->name('editCategory');//view all categories

//--End of Admin api --//


//--Payment Api --//

Route::get('/', function () {
    return view('welcome');
});
Route::post('/confirmOrder/{buyerId}', [CartController::class, 'confirmOrder'])->middleware('auth:sanctum')->name('confirmOrder');//adding to cart api
Route::get('/pay/callback', [CartController::class, 'payment_callback'])->name('pay.callback');
Route::get('/paymentSuccess',  [CartController::class, 'paymentSuccess'])->name('paymentSuccess');

Route::get('/pay', [CartController::class, 'pay']);
Route::post('/pay', [CartController::class, 'make_payment'])->name('pay');

Route::post('initialize_paystack', [CartController::class, 'initialize_paystack'])->name('api.initialize_paystack');
Route::get('/getOrders', [CartController::class, 'getOrders'])->middleware('auth:sanctum')->name('getOrders');
Route::get('/getOrderById/{orderId}', [CartController::class, 'getOrderById'])->middleware('auth:sanctum')->name('getOrderById');
Route::get('/getOrderSuccessful/{orderId}', [CartController::class, 'getOrderSuccessful'])->middleware('auth:sanctum')->name('getOrderSuccessful');
Route::get('/getOrdersFailed/{orderId}', [CartController::class, 'getOrdersFailed'])->middleware('auth:sanctum')->name('getOrdersFailed');