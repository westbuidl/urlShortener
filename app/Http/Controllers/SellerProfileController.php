<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Withdrawal;
use App\Mail\WithdrawalOTP;
use Illuminate\Support\Str;
use App\Mail\WithdrawalOTPc;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use App\Mail\addBankAccountEmail;
use Illuminate\Support\Facades\DB;
use App\Mail\bankAccountSavedEmail;
use App\Mail\WithdrawalConfirmation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Mail\AdminWithdrawalNotification;
use Illuminate\Support\Facades\Validator;

class SellerProfileController extends Controller
{
    //Begin function to change password

    public function changeSellerPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:6|max:100',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }
        $seller = $request->user();
        if ($seller instanceof Seller || $seller instanceof CompanySeller) {
            if (Hash::check($request->old_password, $seller->password)) {
                $seller->update([
                    'password' => Hash::make($request->password)
                ]);
                return response()->json([
                    'message' => 'Password changed'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Old password does not match'
                ], 400);
            }
        }
    } // End function to change password

    // Begin profile picture update function
    public function updateSellerProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'required|image|mimes:jpg,png,bmp|max:1024',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'validations fails',
                'errors' => $validator->errors()
            ], 422);
        }
        $authenticatedUser = $request->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found',
            ], 404);
        }

        if ($request->hasFile('profile_photo')) {
            if ($seller->profile_photo) {
                $old_path = public_path() . '/uploads/profile_images/' . $seller->profile_photo;
                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }
            $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
            $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);

            // Update the seller's profile photo
            $seller->profile_photo = $image_name;
            $seller->save();

            return response()->json([
                'message' => 'Profile Picture successfully updated',
            ], 200);
        } else {
            return response()->json([
                'message' => 'No profile photo uploaded',
            ], 400);
        }
    }

    //Begin update account settings function
    public function updateSellerAccountDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'nullable|max:100',
            'lastname' => 'nullable|max:100',
            'phone' => 'nullable|max:100',
            'country' => 'nullable|max:100',
            'product' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'city' => 'nullable|max:100',
            'zipcode' => 'nullable|max:100',
            'business_address' => 'nullable|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }

        // Get the authenticated user
        $authenticatedUser = $request->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        $isIndividualSeller = true;

        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
            $isIndividualSeller = false;
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Update the seller details
        if ($isIndividualSeller) {
            $seller->update([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'phone' => $request->phone,
                'country' => $request->country,
                'product' => $request->product,
                'state' => $request->state,
                'city' => $request->city,
                'zipcode' => $request->zipcode,
                'address' => $request->business_address,
            ]);
        } else {
            $seller->update([
                'companyname' => $request->firstname,
                'companyphone' => $request->phone,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'product' => $request->product,
                'zipcode' => $request->zipcode,
                'companyaddress' => $request->business_address,
            ]);
        }

        return response()->json([
            'message' => 'Seller account information updated successfully.',
        ], 200);
    }








    //Delete buyer profile picture

    public function deleteSellerProfilePicture(Request $request, $sellerId)
    {
        try {

            $authenticatedUser = $request->user();

            // Determine if the authenticated user is an individual seller or a company seller
            $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
            if (!$seller) {
                $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
            }

            // Ensure the authenticated user is the owner of the account being modified
            if ($authenticatedUser->sellerId != $sellerId && $authenticatedUser->companySellerId != $sellerId) {
                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            // Ensure the seller exists
            if (!$seller) {
                return response()->json([
                    'message' => 'Seller not found.',
                ], 404);
            }

            // Check if the seller has a profile picture
            if (!empty($seller->profile_photo)) {
                // Delete the profile picture from the filesystem
                $imagePath = public_path('/uploads/profile_images/' . $seller->profile_photo);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }

                // Update the buyer's profile picture field to null
                $seller->profile_photo = null;
                $seller->save();


                return response()->json([
                    'message' => 'Profile picture deleted successfully.',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No profile picture found for this seller.',
                ], 400);
            }
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'message' => 'Error deleting profile picture.',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }

    public function getSellerProfile(Request $request, $sellerId)
    {
        try {
            $authenticatedSeller = $request->user();

            // Check if the authenticated user is the one being requested
            if (!($authenticatedSeller->sellerId == $sellerId || $authenticatedSeller->companySellerId == $sellerId)) {
                return response()->json([
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            // Find the seller in the Seller or CompanySeller models
            $seller = Seller::where('sellerId', $sellerId)->first();
            if (!$seller) {
                $seller = CompanySeller::where('companySellerId', $sellerId)->first();
            }

            if ($seller) {
                // Get the profile picture URL
                $profile_picture = asset('uploads/profile_images/' . $seller->profile_photo);

                return response()->json([
                    'message' => 'Seller profile found.',
                    'data' => [
                        'seller' => $seller,
                        'profile_picture' => $profile_picture
                    ]
                ], 200);
            } else {
                // If the seller is not found, return an error message
                return response()->json([
                    'message' => 'Seller not found.',
                ], 404);
            }
        } catch (\Exception $e) {
            // Handle any exceptions that occur
            return response()->json([
                'message' => 'Error retrieving seller profile.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function addBankAccount(Request $request)
    {
        // Get the authenticated seller's ID
        // Get the authenticated user's ID
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|min:2|max:100',
            'account_number' => 'required|min:2|max:100',
            'bank_name' => 'required|min:2|max:100'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        // Extract first name and last name from seller's account
        if ($seller instanceof Seller) {
            $sellerFullName = $seller->firstname . ' ' . $seller->lastname;
            $email = $seller->email;
            $name = $seller->firstname;
        } else {
            $sellerFullName = $seller->companyname;
            $email = $seller->companyemail;
            $name = $seller->companyname;
        }


        // Check if the entered account name matches the seller's full name
        if ($request->account_name !== $sellerFullName) {
            return response()->json([
                'message' => 'Bank account name does not match your registered name.'
            ], 400);
        }

        // Update seller's bank account information
        $seller->account_name = $request->account_name;
        $seller->account_number = $request->account_number;
        $seller->bank_name = $request->bank_name;
        $seller->save();

        // Clear OTP after successful verification
        $seller->verification_code = null;
        $seller->save();


        Mail::to($email)->send(new bankAccountSavedEmail($seller, $name));
        return response()->json([
            'message' => 'Bank account information successfully added.',
            'data' => $seller
        ], 200);
    }

    public function getBankAccountDetails(Request $request)
    {
        // Get the authenticated seller's ID
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Check if seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Check if the seller has bank account details
        if (!$seller->bank_name || !$seller->account_number) {
            return response()->json([
                'message' => 'Seller bank account details not found.',
            ], 404);
        }

        // Construct response with seller's bank account details
        $bankAccountDetails = [
            'account_name' => $seller->account_name,
            'bank_name' => $seller->bank_name,
            'account_number' => $seller->account_number,
        ];

        return response()->json([
            'message' => 'Seller bank account details retrieved successfully.',
            'data' => $bankAccountDetails
        ], 200);
    }


    public function getRecentSales(Request $request)
    {
        // Get the authenticated seller
        // Get the authenticated user's ID
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Fetch recent sales (last 10 orders) for the authenticated seller
        $recentSales = Order::where('sellerId', $authenticatedUser->sellerId)
            ->orWhere('companySellerId', $authenticatedUser->companySellerId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Prepare the sales data with product images
        $salesData = $recentSales->map(function ($order) {
            // Initialize the first product image as null
            $firstProductImage = null;

            // Split the productImage string into an array
            $productImages = explode(',', $order->productImage);

            // Check if the array is not empty and set the first product image
            if (!empty($productImages) && isset($productImages[0]) && $productImages[0] !== '') {
                $firstProductImage = asset('uploads/product_images/' . $productImages[0]);
            }

            return [
                'order_id' => $order->orderId,
                'product_id' => $order->productId,
                'product_name' => $order->productName,
                'product_image' => $firstProductImage,
                'quantity' => $order->quantity,
                'total_price' => $order->grand_price,
                'order_date' => $order->created_at,
            ];
        });

        return response()->json([
            'message' => 'Recent sales found.',
            'data' => [
                'recent_sales' => $salesData,
            ]
        ], 200);
    }



    public function getAllSales(Request $request)
    {
        // Get the authenticated seller
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Fetch all sales for the authenticated seller
        $allSales = Order::where('sellerId', $authenticatedUser->sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalOrders = $allSales->count();
        $totalVolume = $allSales->sum('grand_price');
        $totalProfit = $totalVolume * (1 - 0.08);

        // Calculate completed and pending sales
        $completedSales = $allSales->where('order_status', 'success')->count();
        $pendingSales = $allSales->where('status', 'pending')->count();

        // Fetch the total number of products uploaded by the seller
        $totalProducts = Product::where('sellerId', $authenticatedUser->sellerId)
            ->count();

        // Prepare the sales data with product images
        $salesData = $allSales->map(function ($order) {
            $firstProductImage = null;
            $productImages = explode(',', $order->productImage);
            if (!empty($productImages) && isset($productImages[0]) && $productImages[0] !== '') {
                $firstProductImage = asset('uploads/product_images/' . $productImages[0]);
            }

            return [
                'order_id' => $order->orderId,
                'product_id' => $order->productId,
                'product_name' => $order->productName,
                'product_image' => $firstProductImage,
                'quantity' => $order->quantity,
                'total_price' => $order->grand_price,
                'order_date' => $order->created_at,
            ];
        });

        // Fetch top-selling categories with product count
        $topCategories = Order::where('orders.sellerId', $authenticatedUser->sellerId)
            ->join('products', 'orders.productId', '=', 'products.productId')
            ->join('categories', 'products.categoryID', '=', 'categories.categoryID')
            ->select(
                'categories.categoryName',
                DB::raw('COUNT(DISTINCT orders.productId) as sales_count'),
                DB::raw('SUM(orders.grand_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT products.productId) as product_count')
            )
            ->groupBy('categories.categoryID', 'categories.categoryName')
            ->orderByDesc('sales_count')
            ->limit(5)
            ->get();

        // Fetch total sales in last 7 days
        $salesLast7Days = $this->getSalesLast7Days($authenticatedUser->sellerId);

        return response()->json([
            'message' => 'All sales found.',
            'data' => [
                'all_sales' => $salesData,
                'total_orders' => $totalOrders,
                'total_volume' => $totalVolume,
                'total_profit' => $totalProfit,
                'total_products' => $totalProducts,
                'completed_sales' => $completedSales,
                'pending_sales' => $pendingSales,
                'top_categories' => $topCategories,
                'sales_last_7_days' => $salesLast7Days,
            ]
        ], 200);
    }

    private function getSalesLast7Days($sellerId)
    {
        $sevenDaysAgo = now()->subDays(7)->startOfDay();

        return Order::where('sellerId', $sellerId)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->sum('grand_price');
    }




    public function getTopSellingProducts(Request $request)
    {
        // Get the authenticated seller
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Use the sellerId for querying orders
        $sellerId = $authenticatedUser->sellerId;

        // Aggregate sales data based on products
        $topSellingProducts = Order::where('sellerId', $sellerId)
            ->select('productId', 'productName', 'productImage', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('productId', 'productName', 'productImage')
            ->orderByDesc('total_quantity')
            ->take(10)
            ->get();

        // Check if there are any top selling products
        if ($topSellingProducts->isEmpty()) {
            return response()->json([
                'message' => 'No top selling products found.',
                'data' => [
                    'top_selling_products' => [],
                ]
            ], 200);
        }

        // Prepare the product details along with total quantity sold
        $productDetails = $topSellingProducts->map(function ($product) {
            return [
                'product_id' => $product->productId,
                'product_name' => $product->productName,
                'product_image' => $product->productImage ? asset('uploads/product_images/' . $product->productImage) : null,
                'total_quantity_sold' => $product->total_quantity,
            ];
        });

        return response()->json([
            'message' => 'Top selling products found.',
            'data' => [
                'top_selling_products' => $productDetails,
            ]
        ], 200);
    }


    //Begin Seller Wallet Withdrawal
    public function initiateWithdrawal(Request $request)
    {
        // Validate the request
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get the authenticated seller
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        $isCompany = false;
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
            $isCompany = true;
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        $firstname = $isCompany ? $seller->companyname : $seller->firstname;
        $email = $isCompany ? $seller->companyemail : $seller->email;

        // Check if the seller has sufficient balance
        if ($seller->accrued_profit < $request->amount) {
            return response()->json([
                'message' => 'Insufficient funds in the account.',
            ], 400);
        }

        // Generate OTP
        $otp = Str::random(6);

        // Generate a unique withdrawal ID
        $withdrawalId = Str::uuid();

        // Store OTP and withdrawal details in cache for 10 minutes
        $cacheKey = $isCompany ? 'withdrawal_' . $seller->companySellerId : 'withdrawal_' . $seller->sellerId;
        Cache::put($cacheKey, [
            'otp' => $otp,
            'withdrawal_id' => $withdrawalId,
            'amount' => $request->amount,
            'seller_id' => $isCompany ? $seller->companySellerId : $seller->sellerId,
            'seller_type' => $isCompany ? 'company' : 'individual',
        ], 600);

        // Send OTP to seller's email
        Mail::to($email)->send(new WithdrawalOTP($otp, $firstname, $request->amount));

        return response()->json([
            'message' => 'Withdrawal initiated. Please check your email for the OTP.',
            'withdrawal_id' => $withdrawalId,
        ], 200);
    }

    public function confirmWithdrawal(Request $request)
    {
        // Validate the request
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        // Get the authenticated seller
        $authenticatedUser = auth()->user();

        // Determine if the user is an individual seller or a company seller
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        $isCompany = false;
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
            $isCompany = true;
        }

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        $firstname = $isCompany ? $seller->companyname : $seller->firstname;
        $email = $isCompany ? $seller->companyemail : $seller->email;

        // Retrieve withdrawal details from cache
        $cacheKey = $isCompany ? 'withdrawal_' . $seller->companySellerId : 'withdrawal_' . $seller->sellerId;
        $withdrawalDetails = Cache::get($cacheKey);

        if (!$withdrawalDetails || $withdrawalDetails['otp'] !== $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP.',
            ], 400);
        }

        // Check if the seller still has sufficient balance
        if ($seller->accrued_profit < $withdrawalDetails['amount']) {
            return response()->json([
                'message' => 'Insufficient funds in the account.',
            ], 400);
        }

        // Process the withdrawal
        $seller->accrued_profit -= $withdrawalDetails['amount'];
        $seller->save();

        // Store the withdrawal request in the withdrawals database
        $withdrawal = new Withdrawal();
        $withdrawal->withdrawal_id = $withdrawalDetails['withdrawal_id'];
        $withdrawal->seller_id = $withdrawalDetails['seller_id'];
        $withdrawal->seller_type = $withdrawalDetails['seller_type'];
        $withdrawal->amount = $withdrawalDetails['amount'];
        $withdrawal->status = 'submitted';
        $withdrawal->initiated_at = now()->subMinutes(10); // Assuming it was initiated 10 minutes ago
        $withdrawal->completed_at = now();
        $withdrawal->save();

        // Clear the withdrawal details from cache
        Cache::forget($cacheKey);

        Mail::to($email)->send(new WithdrawalConfirmation($firstname, $withdrawalDetails['amount']));

        // Send notification email to admin
        $adminEmail = 'westwizo@yahoo.com'; // Assuming you have this set in your config
        Mail::to($adminEmail)->send(new AdminWithdrawalNotification($withdrawal));

        return response()->json([
            'message' => 'Withdrawal successful.',
            'new_balance' => $seller->accrued_profit,
            'withdrawal_record_id' => $withdrawal->id,
        ], 200);
    }

    //End



    public function getBalance()
{
    $seller = $this->getAuthenticatedSeller();

    if (!$seller) {
        return response()->json([
            'message' => 'Seller not found.',
        ], 404);
    }

    return response()->json([
        'message' => 'Balance retrieved successfully.',
        'data' => [
            'balance' => $seller->accrued_profit,
        ]
    ], 200);
}

public function getWithdrawals(Request $request)
{
    $seller = $this->getAuthenticatedSeller();

    if (!$seller) {
        return response()->json([
            'message' => 'Seller not found.',
        ], 404);
    }

    $withdrawals = Withdrawal::where('seller_id', $seller instanceof Seller ? $seller->sellerId : $seller->companySellerId)
        ->where('seller_type', $seller instanceof Seller ? 'individual' : 'company')
        ->orderBy('completed_at', 'desc')
        ->paginate($request->get('per_page', 10));

    return response()->json([
        'message' => 'Withdrawals retrieved successfully.',
        'data' => $withdrawals
    ], 200);
}

public function getWithdrawalDetails($withdrawalId)
{
    $seller = $this->getAuthenticatedSeller();

    if (!$seller) {
        return response()->json([
            'message' => 'Seller not found.',
        ], 404);
    }

    $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)
        ->where('seller_id', $seller instanceof Seller ? $seller->sellerId : $seller->companySellerId)
        ->where('seller_type', $seller instanceof Seller ? 'individual' : 'company')
        ->first();

    if (!$withdrawal) {
        return response()->json([
            'message' => 'Withdrawal not found.',
        ], 404);
    }

    return response()->json([
        'message' => 'Withdrawal details retrieved successfully.',
        'data' => $withdrawal
    ], 200);
}

private function getAuthenticatedSeller()
{
    $user = Auth::user();

    if ($user->sellerId) {
        return Seller::where('sellerId', $user->sellerId)->first();
    }

    if ($user->companySellerId) {
        return CompanySeller::where('companySellerId', $user->companySellerId)->first();
    }

    return null;
}



    public function deleteSellerAccount(Request $request, $sellerId)
    {
        try {
            // Retrieve the authenticated user's details
            $authenticatedUser = Auth::user();

            // Determine if the user is an individual seller or a company seller
            $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
            if (!$seller) {
                $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
            }

            // Ensure that the user is authenticated and matches the requested seller ID
            if (!$seller || ($seller instanceof Seller && $seller->sellerId != $sellerId) || ($seller instanceof CompanySeller && $seller->companySellerId != $sellerId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Seller not authenticated or mismatched seller ID.',
                ], 401);
            }

            // Find the seller in the database
            $sellerToDelete = Seller::where('sellerId', $sellerId)->first();
            if (!$sellerToDelete) {
                $sellerToDelete = CompanySeller::where('companySellerId', $sellerId)->first();
            }

            // Check if the seller exists
            if (!$sellerToDelete) {
                return response()->json([
                    'status' => false,
                    'message' => 'Seller not found.',
                ], 404);
            }

            // Delete the profile picture from the filesystem if it exists
            if (!empty($sellerToDelete->profile_photo)) {
                $imagePath = public_path('/uploads/profile_images/' . $sellerToDelete->profile_photo);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }

            // Delete any other associated data if needed (e.g., orders, cart items)

            // Delete the seller's account
            $sellerToDelete->delete();

            return response()->json([
                'status' => true,
                'message' => 'Seller account deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'status' => false,
                'message' => 'Error deleting seller account.',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }
}
