<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Currency;
use App\Models\CompanyBuyer;
//use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use App\Models\ProductFeedback;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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

    public function adminEditProduct(Request $request, string $productId)
    {
        // Check if the user is an admin
        if (!$this->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        // Find the product by its ID
        $product = Product::where('productId', $productId)->first();

        // Check if the product exists
        if (!$product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|min:2|max:100',
            'product_category' => 'required|min:2|max:100',
            'selling_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'quantityin_stock' => 'required|integer|min:0',
            'productWeight' => 'required|numeric|min:0',
            'unit' => 'required|min:2|max:100',
            'product_description' => 'required|min:2',
            'product_image' => 'array|min:2|max:5',
            'product_image.*' => 'image|mimes:jpg,png,bmp',
            //'sellerId' => 'required|exists:sellers,sellerId,companySellers,companySellerId'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation fails',
                'error' => $validator->errors()
            ], 422);
        }

        // Check if the category exists
        $category = Category::where('categoryName', $request->product_category)->first();

        if (!$category) {
            return response()->json([
                'message' => 'Category does not exist',
            ], 404);
        }

        // Category exists, get its ID
        $categoryID = $category->categoryID;

        // Update product details
        $data = [
            'product_name' => $request->product_name,
            'product_category' => $request->product_category,
            'selling_price' => $request->selling_price,
            'categoryID' => $categoryID,
            'cost_price' => $request->cost_price,
            'quantityin_stock' => $request->quantityin_stock,
            'productWeight' => $request->productWeight,
            'unit' => $request->unit,
            'product_description' => $request->product_description,
            //'sellerId' => $request->sellerId
        ];

        // Handle product image updates
        if ($request->hasFile('product_image')) {
            $imageName = '';
            foreach ($request->file('product_image') as $product_image) {
                $new_imageName = rand() . '.' . $product_image->getClientOriginalExtension();
                if ($product->product_image) {
                    // Delete the old product image file
                    $old_images = explode(',', $product->product_image);
                    foreach ($old_images as $old_image) {
                        if (file_exists(public_path('uploads/product_images/' . $old_image))) {
                            unlink(public_path('uploads/product_images/' . $old_image));
                        }
                    }
                }
                // Move and store the new product image file
                $product_image->move(public_path('/uploads/product_images'), $new_imageName);
                $imageName .= $new_imageName . ",";
            }
            $data['product_image'] = rtrim($imageName, ',');
        }

        // Update the product
        $product->update($data);

        // Fetch the updated product with image URLs
        $product = Product::where('productId', $productId)->first();
        $imageURLs = [];
        foreach (explode(',', $product->product_image) as $image) {
            if (!empty($image)) {
                $imageURLs[] = asset('uploads/product_images/' . $image);
            }
        }
        $product->image_urls = $imageURLs;

        // Return success response with the updated product data
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200);
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

        try {
            $searchQuery = $request->query('search_query', null);
            $perPage = $request->query('per_page', 10);

            $query = Buyer::query();

            if ($searchQuery !== null) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('buyerID', $searchQuery)
                        ->orWhere('name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%')
                        ->orWhere('phone', 'like', '%' . $searchQuery . '%');
                });
            }

            $buyers = $query->paginate($perPage);

            if ($buyers->isEmpty()) {
                return response()->json([
                    'message' => 'No buyers found matching the given criteria.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Buyers retrieved successfully.',
                'data' => $buyers->items(),
                'current_page' => $buyers->currentPage(),
                'last_page' => $buyers->lastPage(),
                'per_page' => $buyers->perPage(),
                'total' => $buyers->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while searching buyers: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching for buyers.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function searchProducts(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $searchQuery = $request->query('search_query', null);
            $perPage = $request->query('per_page', 10);

            $query = Product::query();

            if ($searchQuery !== null) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('productID', $searchQuery)
                        ->orWhere('name', 'like', '%' . $searchQuery . '%');
                });
            }

            $products = $query->paginate($perPage);

            if ($products->isEmpty()) {
                return response()->json([
                    'message' => 'No products found matching the given criteria.',
                    'data' => []
                ], 404);
            }

            foreach ($products as $product) {
                $imageURLs = [];
                if (!empty($product->product_image)) {
                    foreach (explode(',', $product->product_image) as $image) {
                        $imageURLs[] = asset('uploads/product_images/' . $image);
                    }
                }
                $product->image_urls = $imageURLs;
            }

            return response()->json([
                'message' => 'Products retrieved successfully.',
                'data' => $products->items(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while searching products: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching for products.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function adminDeleteProduct(string $productId, Request $request)
    {
        try {
            // Check if the user is an admin
            if (!$this->isAdmin()) {
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            // Find the product in the database
            $product = Product::where('productId', $productId)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            // Check if the product has associated images
            if (!empty($product->product_image)) {
                // Split the comma-separated image filenames into an array
                $imageFilenames = explode(',', $product->product_image);

                // Delete associated images from the filesystem
                foreach ($imageFilenames as $filename) {
                    // Assuming images are stored in a folder named 'product_images'
                    $imagePath = public_path('/uploads/product_images/' . $filename);
                    if (File::exists($imagePath)) {
                        File::delete($imagePath);
                    }
                }
            }

            // Delete the product
            $product->delete();

            // Log the deletion action
            $adminId = $request->user() ? $request->user()->id : 'Unknown';
            Log::info("Admin (ID: {$adminId}) deleted product: {$productId}");

            return response()->json([
                'message' => 'Product Deleted Successfully',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            Log::error("Error deleting product {$productId}: " . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function searchCompanyBuyers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $searchQuery = $request->query('search_query', null);
            $perPage = $request->query('per_page', 10);

            $query = CompanyBuyer::query();

            if ($searchQuery !== null) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('companyBuyerID', $searchQuery)
                        ->orWhere('name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%')
                        ->orWhere('phone', 'like', '%' . $searchQuery . '%');
                });
            }

            $companyBuyers = $query->paginate($perPage);

            if ($companyBuyers->isEmpty()) {
                return response()->json([
                    'message' => 'No company buyers found matching the given criteria.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Company buyers retrieved successfully.',
                'data' => $companyBuyers->items(),
                'current_page' => $companyBuyers->currentPage(),
                'last_page' => $companyBuyers->lastPage(),
                'per_page' => $companyBuyers->perPage(),
                'total' => $companyBuyers->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while searching company buyers: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching for company buyers.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function searchSellers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $searchQuery = $request->query('search_query', null);
            $perPage = $request->query('per_page', 10);

            $query = Seller::query();

            if ($searchQuery !== null) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('sellerID', $searchQuery)
                        ->orWhere('name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%')
                        ->orWhere('phone', 'like', '%' . $searchQuery . '%');
                });
            }

            $sellers = $query->paginate($perPage);

            if ($sellers->isEmpty()) {
                return response()->json([
                    'message' => 'No sellers found matching the given criteria.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Sellers retrieved successfully.',
                'data' => $sellers->items(),
                'current_page' => $sellers->currentPage(),
                'last_page' => $sellers->lastPage(),
                'per_page' => $sellers->perPage(),
                'total' => $sellers->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while searching sellers: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching for sellers.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function searchCompanySellers(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $searchQuery = $request->query('search_query', null);
            $perPage = $request->query('per_page', 10);

            $query = CompanySeller::query();

            if ($searchQuery !== null) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('companySellerID', $searchQuery)
                        ->orWhere('name', 'like', '%' . $searchQuery . '%')
                        ->orWhere('email', 'like', '%' . $searchQuery . '%')
                        ->orWhere('phone', 'like', '%' . $searchQuery . '%');
                });
            }

            $companySellers = $query->paginate($perPage);

            if ($companySellers->isEmpty()) {
                return response()->json([
                    'message' => 'No company sellers found matching the given criteria.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Company sellers retrieved successfully.',
                'data' => $companySellers->items(),
                'current_page' => $companySellers->currentPage(),
                'last_page' => $companySellers->lastPage(),
                'per_page' => $companySellers->perPage(),
                'total' => $companySellers->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while searching company sellers: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching for company sellers.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    public function searchOrders(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $searchQuery = $request->query('search_query', null);
            $perPage = $request->query('per_page', 10);

            $query = Order::query();

            if ($searchQuery !== null) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('orderID', $searchQuery)
                        ->orWhere('productID', $searchQuery)
                        ->orWhere('sellerID', $searchQuery)
                        ->orWhereHas('product', function ($subQ) use ($searchQuery) {
                            $subQ->where('name', 'like', '%' . $searchQuery . '%');
                        })
                        ->orWhereHas('seller', function ($subQ) use ($searchQuery) {
                            $subQ->where('name', 'like', '%' . $searchQuery . '%');
                        });
                });
            }

            $orders = $query->paginate($perPage);

            if ($orders->isEmpty()) {
                return response()->json([
                    'message' => 'No orders found matching the given criteria.',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'message' => 'Orders retrieved successfully.',
                'data' => $orders->items(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while searching orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching for orders.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }


    // Add these functions to the AdminController class

    /**
     * Add a new currency
     */
    public function addCurrency(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'currency' => 'required|string|unique:currency,currency|max:10',
                'rate' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create new currency
            $currency = Currency::create([
                'currency' => strtoupper($request->currency),
                'rate' => $request->rate
            ]);

            return response()->json([
                'message' => 'Currency added successfully.',
                'data' => $currency
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error occurred while adding currency: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while adding currency.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    /**
     * Edit existing currency
     */
    public function editCurrency(Request $request, $id)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            // Find the currency
            $currency = Currency::find($id);

            if (!$currency) {
                return response()->json([
                    'message' => 'Currency not found.'
                ], 404);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'currency' => 'required|string|max:10|unique:currency,currency,' . $id,
                'rate' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update the currency
            $currency->update([
                'currency' => strtoupper($request->currency),
                'rate' => $request->rate
            ]);

            return response()->json([
                'message' => 'Currency updated successfully.',
                'data' => $currency
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while updating currency: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while updating currency.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    /**
     * List all currencies
     */
    public function getAllCurrencies()
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $currencies = Currency::all();

            return response()->json([
                'message' => 'Currencies retrieved successfully.',
                'data' => $currencies
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching currencies: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while fetching currencies.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
    }

    /**
     * Delete a currency
     */
    public function deleteCurrency($id)
    {
        if (!$this->isAdmin()) {
            return $this->unauthorizedResponse();
        }

        try {
            $currency = Currency::find($id);

            if (!$currency) {
                return response()->json([
                    'message' => 'Currency not found.'
                ], 404);
            }

            $currency->delete();

            return response()->json([
                'message' => 'Currency deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error occurred while deleting currency: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error occurred while deleting currency.',
                'error' => 'Something went wrong, please try again later.',
            ], 500);
        }
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



    //Fetch product feedback
    public function getProductFeedbacks(Request $request, $productId)
{
    try {
        // Validate the product ID
        if (empty($productId)) {
            return response()->json([
                'success' => false,
                'message' => 'Product ID is required.',
            ], 400);
        }

        // Check if the product exists - add ->first() to get the actual model
        $product = Product::where('productId', $productId)->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found with ID: ' . $productId,
            ], 404);
        }

        // Fetch all feedbacks for the product with buyer information
        $feedbacks = ProductFeedback::where('productId', $productId)
            ->with('buyer:id,firstname,lastname,profile_image')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Check if no reviews were found
        if ($feedbacks->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No reviews found for this product.',
                'product' => [
                    'productId' => $product->productId,
                    'product_name' => $product->product_name,
                    'average_rating' => 0,
                    'total_reviews' => 0,
                ],
                'stats' => [
                    'average_rating' => 0,
                    'total_reviews' => 0,
                    'rating_breakdown' => [
                        5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
                    ],
                ],
                'feedbacks' => [
                    'data' => [],
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
            ], 200);
        }
        
        // Transform the data to explicitly include buyer name
        $transformedFeedbacks = $feedbacks->map(function ($feedback) {
            $buyerName = isset($feedback->buyer) ? 
                $feedback->buyer->firstname . ' ' . $feedback->buyer->lastname : 
                'Anonymous User';
            
            return [
                'id' => $feedback->id,
                'productId' => $feedback->productId,
                'rating' => $feedback->rating,
                'feedback' => $feedback->feedback,
                'created_at' => $feedback->created_at,
                'buyer' => [
                    'id' => $feedback->buyerId ?? null,
                    'name' => $feedback->buyer_fullname,
                    'profile_image' => $feedback->buyer->profile_photo ?? null,
                ],
            ];
        });
        
        // Get summary statistics
        $stats = [
            'average_rating' => ProductFeedback::where('productId', $productId)->avg('rating') ?? 0,
            'total_reviews' => ProductFeedback::where('productId', $productId)->count(),
            'rating_breakdown' => [
                5 => ProductFeedback::where('productId', $productId)->where('rating', 5)->count(),
                4 => ProductFeedback::where('productId', $productId)->where('rating', 4)->count(),
                3 => ProductFeedback::where('productId', $productId)->where('rating', 3)->count(),
                2 => ProductFeedback::where('productId', $productId)->where('rating', 2)->count(),
                1 => ProductFeedback::where('productId', $productId)->where('rating', 1)->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Product feedbacks retrieved successfully.',
            'product' => [
                'productId' => $product->productId,
                'product_name' => $product->product_name,
                'average_rating' => round($stats['average_rating'], 1),
                'total_reviews' => $stats['total_reviews'],
            ],
            'stats' => $stats,
            'feedbacks' => [
                'data' => $transformedFeedbacks,
                'current_page' => $feedbacks->currentPage(),
                'last_page' => $feedbacks->lastPage(),
                'per_page' => $feedbacks->perPage(),
                'total' => $feedbacks->total(),
            ],
        ], 200);
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Error retrieving product feedbacks: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while retrieving product feedbacks.',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }
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
