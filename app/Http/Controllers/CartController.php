<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartController extends Controller
{
    //

    public function storeCart(Request $request)
    {
        $userID = null;

        // Check if the user is authenticated
        if (Auth::guard('api')->check()) {
            $userID = auth('api')->user()->getKey();
        }

        // Create a new cart with the assigned userID
        $cart = Cart::create([
            'id' => md5(uniqid(rand(), true)),
            'key' => md5(uniqid(rand(), true)),
            'userID' => $userID,
        ]);

        return response()->json([
            'Message' => 'A new cart has been created for you!',
            'cartToken' => $cart->id,
            'cartKey' => $cart->key,
        ], 201);
    }






    public function showCart(Request $request)

    {
    }



    public function addToCart(Request $request, $productID)
    {
        try {
            // Check if the user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }
    
            // Validate the request data
            $validator = Validator::make(['product_id' => $productID], [
                'product_id' => 'required|exists:products,id',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Get the authenticated user's ID
            $userId = auth()->id();
    
            // Save product in the carts table
            $item = new Cart;
            $item->session_id = 0; // Assuming session_id is not used
            $item->user_id = $userId;
            $item->product_id = $productID; // Use the $productID parameter here
            $item->quantity = 1; // Assuming quantity is fixed for now
            $item->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Product added to cart',
                'data' => [
                    'cart_item' => $item
                ]
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
    




    public function getAddToCart(Request $request, $id)


    {
        $cartKey =  rand(000000000, 999999999);
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            //'userID' => 'required|min:2|max:100',
            'productID' => 'required|exists:products,product_id',
            'quantity' => 'required|numeric|min:1|max:10',
            'source' => 'required|in:web,app', // Example: Validating 'source' field against two possible values: 'web' or 'app'
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        // Retrieve data from the request
        // $cartKey = $cartKey;
        $productID = $request->input('productID');
        $quantity = $request->input('quantity');
        $source = $request->input('source');

        // Check if the cart key matches the provided cart
        if ($cart->key !== $cartKey) {
            return response()->json([
                'message' => 'The provided cart key does not match the cart key for this cart.',
            ], 400);
        }

        try {
            // Find the product by ID
            $product = Products::findOrFail($productID);

            // Check if the same product is already in the cart
            $cartItem = Cart::where(['cart_id' => $cart->getKey(), 'product_id' => $productID])->first();
            if ($cartItem) {
                // Update the quantity if the product is already in the cart
                $cartItem->quantity = $quantity;
                $cartItem->save(); // Save the updated quantity
            } else {
                // Create a new cart item if the product is not in the cart
                Cart::create(['cart_id' => $cart->getKey(), 'product_id' => $productID, 'quantity' => $quantity]);
            }

            return response()->json(['message' => 'The cart was updated with the given product information successfully'], 200);
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            \Log::error('Error adding product to cart: ' . $e->getMessage());

            return response()->json([
                'message' => 'An error occurred while adding the product to the cart. Please try again later.',
            ], 500);
        }
    }



    public function checkout(Request $request)

    {
    }

    public function deleteCart(Request $request)

    {
    }
}
