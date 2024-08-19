<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\CompanyBuyer;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * View all sellers
     */

    public function adminLogin(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Retrieve the admin by email
        $admin = Admin::where('email', $request->email)->first();

        // Check if the admin exists and password is correct
        if ($admin && Hash::check($request->password, $admin->password)) {
            // Create a new token for the admin
            $token = $admin->createToken('AdminToken')->plainTextToken;

            // Return success response with admin data and token
            return response()->json([
                'message' => 'Login successful.',
                'data' => [
                    'admin' => [
                        'id' => $admin->id,
                        'name' => $admin->name,
                        'email' => $admin->email,
                    ],
                    'token' => $token,
                ]
            ], 200);
        } else {
            // Log the failed login attempt
            Log::warning('Failed login attempt', ['email' => $request->email]);

            // Return error response for invalid credentials
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }
    }




    public function getAllSellers(Request $request)
{
    // Ensure that only authenticated admins can access this endpoint
    $this->middleware('auth:admin');

    try {
        // Fetch individual sellers
        $sellers = Seller::all();

        // Fetch company sellers
        $companySellers = CompanySeller::all();

        // Calculate totals
        $totalIndividualSellers = $sellers->count();
        $totalCompanySellers = $companySellers->count();
        $totalSellers = $totalIndividualSellers + $totalCompanySellers;

        return response()->json([
            'message' => 'Sellers retrieved successfully.',
            'data' => [
                'individual_sellers' => $sellers,
                'company_sellers' => $companySellers,
                'total_individual_sellers' => $totalIndividualSellers,
                'total_company_sellers' => $totalCompanySellers,
                'total_sellers' => $totalSellers,
            ]
        ], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error occurred while fetching sellers: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error occurred while fetching sellers.',
            'error' => 'Something went wrong, please try again later.',
        ], 500);
    }
}





    /**
     * View all buyers
     */
    public function getAllBuyers(Request $request)
{
    // Ensure that only authenticated admins can access this endpoint
    $this->middleware('auth:admin');

    try {
        // Fetch individual buyers
        $buyers = Buyer::all();

        // Fetch company buyers
        $companyBuyers = CompanyBuyer::all();

        // Calculate totals
        $totalIndividualBuyers = $buyers->count();
        $totalCompanyBuyers = $companyBuyers->count();
        $totalBuyers = $totalIndividualBuyers + $totalCompanyBuyers;

        return response()->json([
            'message' => 'Buyers retrieved successfully.',
            'data' => [
                'individual_buyers' => $buyers,
                'company_buyers' => $companyBuyers,
                'total_individual_buyers' => $totalIndividualBuyers,
                'total_company_buyers' => $totalCompanyBuyers,
                'total_buyers' => $totalBuyers,
            ]
        ], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error occurred while fetching buyers: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error occurred while fetching buyers.',
            'error' => 'Something went wrong, please try again later.',
        ], 500);
    }
}



public function getAllProducts(Request $request)
{
    // Ensure that only authenticated admins can access this endpoint
    $this->middleware('auth:admin');

    try {
        // Fetch all products
        $products = Product::all();

        // Calculate total products
        $totalProducts = $products->count();

        return response()->json([
            'message' => 'Products retrieved successfully.',
            'data' => [
                'products' => $products,
                'total_products' => $totalProducts,
            ]
        ], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error occurred while fetching products: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error occurred while fetching products.',
            'error' => 'Something went wrong, please try again later.',
        ], 500);
    }
}

public function getAllOrders(Request $request)
{
    // Ensure that only authenticated admins can access this endpoint
    $this->middleware('auth:admin');

    try {
        // Fetch all orders
        $orders = Order::all();

        // Calculate total orders
        $totalOrders = $orders->count();

        // Calculate the number of orders by status
        $successfulOrders = $orders->where('order_status', 'success')->count();
        $pendingOrders = $orders->where('order_status', 'pending')->count();
        $failedOrders = $orders->where('order_status', 'failed')->count();

        // Calculate the total quantity of products and total order amount
        $totalQuantity = $orders->sum('quantity'); // Assumes there's a 'quantity' field in the Order model
        $totalOrderAmount = $orders->sum('grand_price'); // Assumes there's a 'total_amount' field in the Order model

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'data' => [
                'orders' => $orders,
                'total_orders' => $totalOrders,
                'successful_orders' => $successfulOrders,
                'pending_orders' => $pendingOrders,
                'failed_orders' => $failedOrders,
                'total_quantity' => $totalQuantity,
                'total_order_amount' => $totalOrderAmount,
            ]
        ], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('Error occurred while fetching orders: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error occurred while fetching orders.',
            'error' => 'Something went wrong, please try again later.',
        ], 500);
    }
}


    /**
     * Get count of buyers in each category
     */
    public function getBuyerCountsByCategory()
    {
        try {
            $categories = Category::all();
            $buyerCounts = [];

            foreach ($categories as $category) {
                $individualBuyerCount = Buyer::where('categoryID', $category->categoryID)->count();
                $companyBuyerCount = CompanyBuyer::where('categoryID', $category->categoryID)->count();

                $buyerCounts[] = [
                    'category' => $category->categoryName,
                    'individual_buyers_count' => $individualBuyerCount,
                    'company_buyers_count' => $companyBuyerCount,
                ];
            }

            return response()->json([
                'message' => 'Buyer counts by category retrieved successfully.',
                'data' => $buyerCounts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while fetching buyer counts by category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }








    /**
     * Admin Logout
     */
    public function adminLogout(Request $request)
    {
        // Retrieve the authenticated admin
        $admin = $request->user('admin');

        // Check if an admin is authenticated
        if (!$admin) {
            return response()->json([
                'message' => 'No authenticated admin found.',
            ], 401);
        }

        // Revoke all tokens for the authenticated admin
        $admin->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ], 200);
    }
}
