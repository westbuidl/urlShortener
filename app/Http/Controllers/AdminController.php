<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\Seller;
use App\Models\Category;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * View all sellers
     */
    public function getAllSellers()
    {
        try {
            $sellers = Seller::all();
            $companySellers = CompanySeller::all();

            return response()->json([
                'message' => 'Sellers retrieved successfully.',
                'data' => [
                    'individual_sellers' => $sellers,
                    'company_sellers' => $companySellers,
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


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $admin = Auth::user();
            $token = $admin->createToken('AdminToken')->accessToken;

            return response()->json([
                'message' => 'Login successful.',
                'data' => [
                    'admin' => $admin,
                    'token' => $token,
                ]
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }
    }

    /**
     * Admin Logout
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Logout successful.',
        ], 200);
    }
}
