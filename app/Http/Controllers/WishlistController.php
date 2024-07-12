<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    //Add item to wishlist

    public function addToWishlist(Request $request, $productId)
    {
        $wishlistId = 'WISHLIST' . rand(100000000, 999999999);
        

            try {
                // Retrieve the authenticated user's ID
                //$user_id = Auth::id();
                $buyer = $request->user();
    
                // Ensure that the user is logged in
                if (!$buyer) {
                    return response()->json([
                        'status' => false,
                        'message' => 'User not authenticated.',
                    ], 401);
                }
    
                // Check if the user is an individual buyer
                $individualBuyer = Buyer::where('buyerId', $buyer->buyerId)->first();
                $companyBuyer = CompanyBuyer::where('companyBuyerId', $buyer->companyBuyerId)->first();
    
                // Determine the buyer type and ID
                if ($individualBuyer) {
                    $buyerId = $individualBuyer->buyerId;
                    $buyerType = 'individual';
                } elseif ($companyBuyer) {
                    $buyerId = $companyBuyer->companyBuyerId;
                    $buyerType = 'company';
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid buyer type.',
                    ], 400);
                }

            

            $product = Product::where('productId', $productId)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            // Check if the product already exists in the user's wishlist
            $existingWishlistItem = Wishlist::where('buyerId',  $buyerId)
                ->where('productId', $productId)
                ->first();

            if ($existingWishlistItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product already in wishlist.',
                ], 400);
            }

            // Create a new Wishlist instance and populate it
            $wishlist = new Wishlist;
            $wishlist->wishlistId = $wishlistId;
            $wishlist->buyerId = $buyerId;
            $wishlist->productId = $productId;
            $wishlist->product_image = $product->product_image;
            $wishlist->product_name = $product->product_name;
            $wishlist->product_category = $product->product_category;
            $wishlist->selling_price = $product->selling_price;
            $wishlist->categoryID = $product->categoryID;

            // Save the wishlist
            $wishlist->save();

            // Return a success response
            return response()->json([
                'message' => 'Product added to wishlist successfully.',
                'wishlist' => $wishlist,
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while adding the product to wishlist: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function removeFromWishlist(Request $request, $productId)
    {
        try {
            // Retrieve the authenticated user's ID
            //$user_id = Auth::id();
            $buyer = $request->user();

            // Ensure that the user is logged in
            if (!$buyer) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Check if the user is an individual buyer
            $individualBuyer = Buyer::where('buyerId', $buyer->buyerId)->first();
            $companyBuyer = CompanyBuyer::where('companyBuyerId', $buyer->companyBuyerId)->first();

            // Determine the buyer type and ID
            if ($individualBuyer) {
                $buyerId = $individualBuyer->buyerId;
                $buyerType = 'individual';
            } elseif ($companyBuyer) {
                $buyerId = $companyBuyer->companyBuyerId;
                $buyerType = 'company';
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid buyer type.',
                ], 400);
            }

            // Check if the product exists in the user's wishlist
            $wishlistItem = Wishlist::where('buyerId', $buyerId)
                ->where('productId', $productId)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found in wishlist.',
                ], 404);
            }

            // Delete the wishlist item
            $wishlistItem->delete();

            // Return a success response
            return response()->json([
                'message' => 'Product removed from wishlist successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while removing the product from wishlist: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function viewWishlist(Request $request)
    {
        try {
            // Retrieve the authenticated user's ID
            //$user_id = Auth::id();
            $buyer = $request->user();

            // Ensure that the user is logged in
            if (!$buyer) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Check if the user is an individual buyer
            $individualBuyer = Buyer::where('buyerId', $buyer->buyerId)->first();
            $companyBuyer = CompanyBuyer::where('companyBuyerId', $buyer->companyBuyerId)->first();

            // Determine the buyer type and ID
            if ($individualBuyer) {
                $buyerId = $individualBuyer->buyerId;
                $buyerType = 'individual';
            } elseif ($companyBuyer) {
                $buyerId = $companyBuyer->companyBuyerId;
                $buyerType = 'company';
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid buyer type.',
                ], 400);
            }

            // Retrieve the wishlist items for the authenticated user
            $wishlistItems = Wishlist::where('buyerId', $buyerId)->get();

            // Check if there are any items in the wishlist
            if ($wishlistItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No items found in wishlist.',
                    'data' => [],
                ], 404);
            }

            // Add the full URL to the product images
            $wishlistItems->each(function ($item) {
                $item->product_image_url = $item->product_image ? asset('uploads/product_images/' . $item->product_image) : null;
            });

            // Return the wishlist items
            return response()->json([
                'message' => 'Wishlist items retrieved successfully.',
                'data' => $wishlistItems,
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving the wishlist: ' . $e->getMessage(),
            ], 500);
        }
    }
}
