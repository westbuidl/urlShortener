<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Mail\addBankAccountEmail;
use Illuminate\Support\Facades\DB;
use App\Mail\bankAccountSavedEmail;
use App\Models\CompanySeller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
            //'email' => 'nullable|max:100',
            'phone' => 'nullable|max:100',
            'country' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'city' => 'nullable|max:100',
            'zipcode' => 'nullable|max:100'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }
        $seller = $request->user();
        $seller->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            //'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zipcode' => $request->zipcode


        ]);

        return response()->json([
            'message' => 'Seller Contact information Changed',
        ], 200);
    } //End update account settings function







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
         // Fetch all sales for the authenticated seller
    $allSales = Order::where('sellerId', $authenticatedUser->sellerId)
    ->orderBy('created_at', 'desc')
    ->get();

        //$TotalSales = Order::where('sellerId', $sellerId)->get();
        $totalOrders = $allSales->count();
        $totalVolme = $allSales->sum('grand_price');
        $totalProfit = $totalVolme * (1 - 0.08);

        // Calculate completed and pending sales
        $completedSales = $allSales->where('order_status', 'success')->count();
        $pendingSales = $allSales->where('status', 'pending')->count();

        // Fetch the total number of products uploaded by the seller
        $totalProducts = Product::where('sellerId', $authenticatedUser->sellerId)
                            ->count();


        // Prepare the sales data with product images
        $salesData = $allSales->map(function ($order) {

            $firstProductImage = null;

            // Split the productImage string into an array
            $productImages = explode(',', $order->productImage);

            // Check if the array is not empty and set the first product image
            if (!empty($productImages) && isset($productImages[0]) && $productImages[0] !== '') {
                $firstProductImage = asset('uploads/product_images/' . $productImages[0]);
            }

            //$productImage = json_decode($order->productImage, true);
            //$firstProductImage = isset($productImage[0]) ? asset('uploads/product_images/' . $productImage[0]) : null;

            return [
                'order_id' => $order->orderId,
                'product_id' => $order->productId,
                'product_name' => $order->productName,
                //'product_image' => asset('uploads/product_images/' . $order->productImage[0]),
                //'product_image' => $order->productImage ? asset('uploads/product_images/' . $order->productImage) : null,
                'product_image' => $firstProductImage,
                'quantity' => $order->quantity,
                'total_price' => $order->grand_price,
                'order_date' => $order->created_at,
            ];
        });


        return response()->json([
            'message' => 'All sales found.',
            'data' => [
                'all_sales' => $salesData,
                'total_orders' => $totalOrders,
                'total_volume' => $totalVolme,
                'total_profit' => $totalProfit,
                'total_products' => $totalProducts,
                'completed_sales' => $completedSales,
                'pending_sales' => $pendingSales,
            ]
        ], 200);
    }


    public function getSaleDetails(Request $request, $orderId)
    {
        // Get the authenticated seller
        $authenticatedUser = auth()->user();
    
        // Ensure the seller exists
        $seller = Seller::where('sellerId', $authenticatedUser->sellerId)->first();
        if (!$seller) {
            $seller = CompanySeller::where('companySellerId', $authenticatedUser->companySellerId)->first();
        }
    
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }
    
        // Fetch sale details for the given orderId and authenticated seller
        $order = Order::where('sellerId', $authenticatedUser->sellerId)
                      ->where('orderId', $orderId)
                      ->first();
    
        // Ensure the order exists
        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }
    
        // Prepare sale data with product images
        $productImages = explode(',', $order->productImage);
        $firstProductImage = !empty($productImages) && isset($productImages[0]) && $productImages[0] !== ''
                             ? asset('uploads/product_images/' . $productImages[0])
                             : null;
    
        $saleDetails = [
            'order_id' => $order->orderId,
            'product_id' => $order->productId,
            'product_name' => $order->productName,
            'product_image' => $firstProductImage,
            'quantity' => $order->quantity,
            'total_price' => $order->grand_price,
            'order_date' => $order->created_at,
        ];
    
        return response()->json([
            'message' => 'Sale details found.',
            'data' => $saleDetails
        ], 200);
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
