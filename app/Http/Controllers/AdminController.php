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
        // Check if the authenticated user is an admin
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        
        }

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
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $buyers = Buyer::all();
            $companyBuyers = CompanyBuyer::all();

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
            Log::error('Error occurred while fetching buyers: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching buyers.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    //Edit buyer
    public function editBuyer(Request $request, $buyerId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $buyer = Buyer::find($buyerId) ?? CompanyBuyer::find($buyerId);

            if (!$buyer) {
                return response()->json(['message' => 'Buyer not found'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:buyers,email,' . $buyerId . ',buyerID|unique:company_buyers,email,' . $buyerId . ',buyerID',
                // Add other fields as needed
            ]);

            $buyer->update($validatedData);

            return response()->json([
                'message' => 'Buyer updated successfully',
                'data' => $buyer
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while editing buyer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while editing buyer.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }
//End edit buyer

//Start delete buyer

public function deleteBuyer($buyerId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $buyer = Buyer::find($buyerId) ?? CompanyBuyer::find($buyerId);

            if (!$buyer) {
                return response()->json(['message' => 'Buyer not found'], 404);
            }

            $buyer->delete();

            return response()->json([
                'message' => 'Buyer deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while deleting buyer: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while deleting buyer.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

//End Delete buyer

//Start get Buyer Details
public function getBuyerDetails($buyerId)
{
    if (!$this->isAdmin()) {
        return $this->unauthorizedResponse();
    }

    try {
        $buyer = $this->findBuyer($buyerId);

        if (!$buyer) {
            return response()->json(['message' => 'Buyer not found'], 404);
        }

        return response()->json([
            'message' => 'Buyer details retrieved successfully',
            'data' => $buyer
        ], 200);
    } catch (\Exception $e) {
        Log::error('Error occurred while fetching buyer details: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error occurred while fetching buyer details.',
            'error' => 'Something went wrong, please try again later.',
        ], 500);
    }
}

public function editSeller(Request $request, $sellerId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $seller = $this->findSeller($sellerId);

            if (!$seller) {
                return response()->json(['message' => 'Seller not found'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:sellers,email,' . $sellerId . ',' . $this->getSellerIdColumnName($seller) . '|unique:company_sellers,email,' . $sellerId . ',' . $this->getSellerIdColumnName($seller),
                // Add other fields as needed
            ]);

            $seller->update($validatedData);

            return response()->json([
                'message' => 'Seller updated successfully',
                'data' => $seller
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while editing seller: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while editing seller.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function deleteSeller($sellerId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $seller = $this->findSeller($sellerId);

            if (!$seller) {
                return response()->json(['message' => 'Seller not found'], 404);
            }

            $seller->delete();

            return response()->json([
                'message' => 'Seller deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while deleting seller: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while deleting seller.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function getSellerDetails($sellerId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $seller = $this->findSeller($sellerId);

            if (!$seller) {
                return response()->json(['message' => 'Seller not found'], 404);
            }

            return response()->json([
                'message' => 'Seller details retrieved successfully',
                'data' => $seller
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching seller details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching seller details.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }



    public function getAllProducts(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $products = Product::all();
            $totalProducts = $products->count();

            return response()->json([
                'message' => 'Products retrieved successfully.',
                'data' => [ 
                    'products' => $products,
                    'total_products' => $totalProducts,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching products: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching products.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function getProductDetails($productId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $product = Product::where('productID', $productId)
                              ->with(['category', 'seller'])
                              ->first();

            if (!$product) {
                return response()->json([
                    'message' => 'Product not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Product details retrieved successfully.',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching product details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching product details.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function editProduct(Request $request, $productId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $product = Product::find($productId);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'price' => 'sometimes|numeric|min:0',
                'categoryID' => 'sometimes|exists:categories,categoryID',
                'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $product->update($validatedData);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('product_images', 'public');
                    $product->images()->create(['image_path' => $path]);
                }
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => $product->load('images')
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while editing product: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while editing product.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function getAllOrders(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $orders = Order::all();
            $totalOrders = $orders->count();
            $successfulOrders = $orders->where('order_status', 'success')->count();
            $pendingOrders = $orders->where('order_status', 'pending')->count();
            $failedOrders = $orders->where('order_status', 'failed')->count();
            $totalQuantity = $orders->sum('quantity');
            $totalOrderAmount = $orders->sum('grand_price');

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
            Log::error('Error occurred while fetching orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching orders.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function getOrderDetails($orderId)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $order = Order::where('orderId', $orderId)
                          //->with(['buyer', 'seller', 'product'])
                          ->first();

            if (!$order) {
                return response()->json([
                    'message' => 'Order not found.',
                ], 404);
            }

            return response()->json([
                'message' => 'Order details retrieved successfully.',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching order details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching order details.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    



    public function getBuyerCountsByCategory()
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

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
            Log::error('Error occurred while fetching buyer counts by category: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching buyer counts by category.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function search(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = $request->query('q');

        $buyers = Buyer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        $companyBuyers = CompanyBuyer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        $sellers = Seller::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        $companySellers = CompanySeller::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->get();

        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->get();

        $orders = Order::where('orderId', 'LIKE', "%{$query}%")
            ->orWhereHas('product', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->get();

        $categories = Category::where('categoryName', 'LIKE', "%{$query}%")
            ->get();

        return response()->json([
            'buyers' => $buyers,
            'companyBuyers' => $companyBuyers,
            'sellers' => $sellers,
            'companySellers' => $companySellers,
            'products' => $products,
            'orders' => $orders,
            'categories' => $categories,
        ]);
    }


    public function searchBuyers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = Buyer::query();

        if ($request->has('name')) {
            $query->where('name', 'LIKE', "%{$request->name}%");
        }
        if ($request->has('buyerId')) {
            $query->where('buyerID', $request->buyerId);
        }
        if ($request->has('email')) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        }
        if ($request->has('phone')) {
            $query->where('phone', 'LIKE', "%{$request->phone}%");
        }

        $buyers = $query->get();

        return response()->json([
            'buyers' => $buyers,
        ]);
    }

    public function searchCompanyBuyers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = CompanyBuyer::query();

        if ($request->has('name')) {
            $query->where('name', 'LIKE', "%{$request->name}%");
        }
        if ($request->has('buyerId')) {
            $query->where('companyBuyerID', $request->buyerId);
        }
        if ($request->has('email')) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        }
        if ($request->has('phone')) {
            $query->where('phone', 'LIKE', "%{$request->phone}%");
        }

        $companyBuyers = $query->get();

        return response()->json([
            'companyBuyers' => $companyBuyers,
        ]);
    }

    public function searchSellers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = Seller::query();

        if ($request->has('name')) {
            $query->where('name', 'LIKE', "%{$request->name}%");
        }
        if ($request->has('sellerId')) {
            $query->where('sellerID', $request->sellerId);
        }
        if ($request->has('email')) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        }
        if ($request->has('phone')) {
            $query->where('phone', 'LIKE', "%{$request->phone}%");
        }

        $sellers = $query->get();

        return response()->json([
            'sellers' => $sellers,
        ]);
    }

    public function searchCompanySellers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = CompanySeller::query();

        if ($request->has('name')) {
            $query->where('name', 'LIKE', "%{$request->name}%");
        }
        if ($request->has('sellerId')) {
            $query->where('companySellerID', $request->sellerId);
        }
        if ($request->has('email')) {
            $query->where('email', 'LIKE', "%{$request->email}%");
        }
        if ($request->has('phone')) {
            $query->where('phone', 'LIKE', "%{$request->phone}%");
        }

        $companySellers = $query->get();

        return response()->json([
            'companySellers' => $companySellers,
        ]);
    }

    public function searchProducts(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = Product::query();

        if ($request->has('name')) {
            $query->where('name', 'LIKE', "%{$request->name}%");
        }
        if ($request->has('productId')) {
            $query->where('productID', $request->productId);
        }
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('categoryName', 'LIKE', "%{$request->category}%");
            });
        }

        $products = $query->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function searchOrders(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        $query = Order::query();

        if ($request->has('orderId')) {
            $query->where('orderID', $request->orderId);
        }
        if ($request->has('productName')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->productName}%");
            });
        }
        if ($request->has('productId')) {
            $query->where('productID', $request->productId);
        }
        if ($request->has('sellerName')) {
            $query->whereHas('seller', function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->sellerName}%");
            });
        }
        if ($request->has('sellerId')) {
            $query->where('sellerID', $request->sellerId);
        }

        $orders = $query->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }



    private function isAdmin()
    {
        return Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user() instanceof Admin;
    }


    private function unauthorizedResponse()
    {
        return response()->json([
            'message' => 'Unauthorized. Admin access required.',
        ], 403);
    }

    private function findBuyer($buyerId)
    {
        return Buyer::where('buyerID', $buyerId)->first() ?? CompanyBuyer::where('companyBuyerID', $buyerId)->first();
    }

    private function findSeller($sellerId)
    {
        return Seller::where('sellerID', $sellerId)->first() ?? CompanySeller::where('companySellerID', $sellerId)->first();
    }

    private function getBuyerIdColumnName($buyer)
    {
        return $buyer instanceof Buyer ? 'buyerID' : 'companyBuyerID';
    }

    private function getSellerIdColumnName($seller)
    {
        return $seller instanceof Seller ? 'sellerID' : 'companySellerID';
    }








    /**
     * Admin Logout
     */
    public function adminLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful.',
        ], 200);
    }
}