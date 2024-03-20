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

    public function showCart(Request $request)

    {
    }

    public function addToCart(Cart $cart, Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            //'cartKey' => 'required',
            'productID' => 'required|exists:products,product_id', // Check if the productID exists in the products table
            'quantity' => 'required|numeric|min:1|max:10',
        ]);
    
        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }
    
        // Retrieve data from the request
        //$cartKey = $request->input('cartKey');
        $productID = $request->input('productID');
        $quantity = $request->input('quantity');
    
        // Check if the cart key matches the provided cart
       /* if ($cart->key !== $cartKey) {
            return response()->json([
                'message' => 'The provided cart key does not match the cart key for this cart.',
            ], 400);
        }*/
    
        // Check if the product exists or return 404 not found
        try {
            $product = Products::findOrFail($productID);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'The product you\'re trying to add does not exist.',
            ], 404);
        }
    
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
    }
    

    public function checkout(Request $request)

    {
    }

    public function deleteCart(Request $request)

    {
    }
}
