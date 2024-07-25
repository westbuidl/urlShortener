<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\Admin;
use App\Models\Seller;
use App\Models\Category;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
//use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Middleware\AdminMiddleware;

class AdminController extends Controller
{
    /**
     * View all sellers
     */
    public function getAllSellers(Request $request)
    {
        try {
            // Fetch individual sellers
            $sellers = Seller::all();
            // Fetch company sellers
            $companySellers = CompanySeller::all();

            // Calculate total numbers
            $totalIndividualSellers = $sellers->count();
            $totalCompanySellers = $companySellers->count();

            return response()->json([
                'message' => 'Sellers retrieved successfully.',
                'data' => [
                    'individual_sellers' => $sellers,
                    'company_sellers' => $companySellers,
                    'total_individual_sellers' => $totalIndividualSellers,
                    'total_company_sellers' => $totalCompanySellers,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while fetching sellers.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * View all buyers
     */
    public function getAllBuyers()
    {
        try {
            $buyers = Buyer::all();
            $companyBuyers = CompanyBuyer::all();

            return response()->json([
                'message' => 'Buyers retrieved successfully.',
                'data' => [
                    'individual_buyers' => $buyers,
                    'company_buyers' => $companyBuyers,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while fetching buyers.',
                'error' => $e->getMessage(),
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
                    'admin' => $admin,
                    'token' => $token,
                ]
            ], 200);
        } else {
            // Return error response for invalid credentials
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
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
