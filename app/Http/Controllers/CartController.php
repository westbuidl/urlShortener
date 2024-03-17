<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    //







    public function addToCart(Request $request)
{
    if ($request->isMethod('post')) {
        // Get the logged-in user
        $user = Auth::user();

        // Validate request data
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Fetch the product
        $product = Product::find($request->product_id);

        // Check if the product exists
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found', 
            ], 404);
        }

        // Save product to cart
        $item = new Cart;
        $item->user_id = $user->id;
        $item->product_id = $product->id;
        $item->quantity = $request->quantity;
        $item->amount = $product->price * $request->quantity;
        $item->source = "App";
        // Assuming 'size' is a field in your Cart model, set it accordingly
        $item->size = $request->size ?? null;
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart',
        ], 200);
    } else {
        return response()->json([
            'status' => false,
            'message' => 'Method not allowed',
        ], 405);
    }
}
   /* public function addToCart(Request $request){
        if($request->isMethod('post')){
            $data = $request->input();
            //save products to products table
            $item = new Cart;
            $item->session_id = 0;
            $item->user_id = $data['user_id'];
            $item->product_id = $data['product_id'];
            $item->quantity = $data['quantity'];
            $item->amount = $data['amount'];
            $item->source = "App";
            $item->size ();

            return response()->json([
                'status'=>true,
                "message"=>"Product added to cart"
            ], 200);
        }
     }*/
}
