<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\BuyerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartController extends Controller
{
    //

    public function addToCart(Request $request, $product_id)


    {
        $cart_id = 'CART' . rand(000000000, 999999999);
        try {
            // Retrieve the authenticated user's ID
            //$user_id = Auth::id();
            $buyer = $request->user();

            // Ensure that the user is logged in
            if (!$buyer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Buyer not authenticated.',
                ], 401);
            }

            // Retrieve data from the request
            $data = $request->validate([
                'quantity' => 'required|integer',
            ]);


            $product = Product::where('product_id', $product_id)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            // Check if the product already exists in the user's cart
            $existingCartItem = Cart::where('buyerId',  $buyer->buyerId)
                ->where('product_id', $product_id)
                ->first();

            if ($existingCartItem) {
                // If the product already exists in the cart, increment the quantity
                $existingCartItem->quantity += $data['quantity'];
                $existingCartItem->total_price += $data['quantity'] * $product->selling_price;
                $existingCartItem->save();
                $cart = $existingCartItem; // Return the updated cart item
            } else {

                // Create a new Cart instance and populate it
                $cart = new Cart;
                $cart->cart_id = $cart_id;
                $cart->buyerId = $buyer->buyerId;
                $cart->product_id = $product_id;
                $cart->product_image = $product->product_image;
                $cart->product_name = $product->product_name;
                $cart->product_category = $product->product_category;
                $cart->selling_price = $product->selling_price;
                $cart->quantity = $data['quantity'];
                $cart->total_price = $data['quantity'] * $product->selling_price;
                $cart->categoryID = $product->categoryID;

                // Save the cart
                $cart->save();
            }
            // Return a success response
            return response()->json([
                //'status' => true,
                'message' => 'Product added to cart successfully.',
                'cart' => $cart,
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while adding the product to cart: ' . $e->getMessage(),
            ], 500);
        }
    }



    //View cart

    public function viewCart()
    {
        try {
            // Retrieve the authenticated user
            $buyer = Auth::user();

            // Ensure that the user is logged in
            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Retrieve the cart items for the authenticated user
            $cartItems = Cart::where('buyerId', $buyer->buyerId)
                ->orderByDesc('id')
                ->get();
            // ->get();

            $products_in_cart_count = Cart::where('buyerId', $buyer->buyerId)->count();
            //$product_quantity__in_cart = Cart::where('user_id', $user->quantity)->count();

            $totalPrice = 0; // Initialize total price variable
            $totalQuantity = 0;

            // Add image URLs to the product object

            // Calculate total price
            foreach ($cartItems as $item) {
                $totalQuantity += $item->quantity;
                $totalPrice += $item->selling_price * $item->quantity;
                $product = Product::find($item->product_id);
                $item->product_image_url = asset('uploads/product_images/' . $item->product_image);
            }




            // Return the cart items
            return response()->json([
                'status' => true,
                'message' => 'Cart items retrieved successfully.',
                'cart_items' => $cartItems,
                'products_in_cart' => $products_in_cart_count,
                'total_Price' => $totalPrice,
                'total_Quantity' => $totalQuantity
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving cart items: ' . $e->getMessage(),
            ], 500);
        }
    }

    //Delete cart Item

    public function deleteCartItem($cart_id)
    {
        try {
            // Retrieve the authenticated user
            $buyerId = Auth::user();

            // Ensure that the user is logged in
            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Retrieve the cart item
            // $cartItem = Cart::find($cart_id);
            $cartItem = Cart::where('cart_id', $cart_id)->first();

            // Check if the cart item exists
            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found.',
                ], 404);
            }

            // Ensure that the cart item belongs to the authenticated user
            if ($cartItem->buyerId !== $buyer->buyerId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            // Delete the cart item
            $cartItem->delete();

            return response()->json([
                'status' => true,
                'message' => 'Cart item deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the cart item: ' . $e->getMessage(),
            ], 500);
        }
    }

    //Update cart Item

    public function updateCartItem(Request $request, $cart_id)
    {
        try {
            // Retrieve the authenticated user
            $buyerId = Auth::user();

            // Ensure that the user is logged in
            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }

            // Retrieve the cart item
            $cartItem = Cart::where('cart_id', $cart_id)->first();

            // Check if the cart item exists
            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart item not found.',
                ], 404);
            }

            // Ensure that the cart item belongs to the authenticated user
            if ($cartItem->buyerId !== $buyer->buyerId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            // Validate the request data
            $request->validate([
                'new_quantity' => 'required|integer|min:1', // Minimum quantity is 1
            ]);

            // Fetch the product details using the cart item's product_id
            $product = Product::where('product_id', $cartItem->product_id)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            // Update the quantity of the cart item
            $cartItem->quantity = $request->new_quantity;
            $cartItem->total_price = $request->new_quantity * $product->selling_price;
            $cartItem->save();

            return response()->json([
                'status' => true,
                'message' => 'Cart item quantity updated successfully.',
                'cart_item' => $cartItem,
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the cart item: ' . $e->getMessage(),
            ], 500);
        }
    }
}
