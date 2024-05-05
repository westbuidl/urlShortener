<?php

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Unicodeveloper\Paystack\Paystack;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartController extends Controller
{
    //

    public function addToCart(Request $request, $productId)


    {
        $cartId = 'CART' . rand(100000000, 999999999);
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


            $product = Product::where('productId', $productId)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found.',
                ], 404);
            }
            // Retrieve data from the request
            $data = $request->validate([
                'quantity' => 'required|integer',
            ]);


            $quantity = $data['quantity'];

            // Check if the requested quantity is available in stock
            if ($quantity > $product->quantityin_stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'Requested quantity exceeds available stock.',
                ], 400);
            }







            // Check if the product already exists in the user's cart
            $existingCartItem = Cart::where('buyerId',  $buyer->buyerId)
                ->where('productId', $productId)
                ->first();

            if ($existingCartItem) {
                // Calculate the total quantity after adding the requested quantity
                $totalQuantity = $existingCartItem->quantity + $data['quantity'];

                // Check if the total quantity exceeds the available stock
                if ($totalQuantity > $product->quantityin_stock) {
                    // Calculate the available quantity that can be added to the cart
                    $availableQuantity = $product->quantityin_stock - $existingCartItem->quantity;

                    return response()->json([
                        'status' => false,
                        'message' => 'Requested quantity exceeds available stock. Maximum available quantity: ' . $availableQuantity,
                    ], 400);
                }

                // If the product already exists in the cart and requested quantity is within available stock, update the quantity
                $existingCartItem->quantity += $data['quantity'];
                $existingCartItem->total_price += $data['quantity'] * $product->selling_price;
                $existingCartItem->save();
                $cart = $existingCartItem; // Return the updated cart item
                //}

            } else {

                // Create a new Cart instance and populate it
                $cart = new Cart;
                $cart->cartId = $cartId;
                $cart->buyerId = $buyer->buyerId;
                $cart->productId = $productId;
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
                $product = Product::find($item->productId);
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

    public function deleteCartItem($cartId)
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

            // Retrieve the cart item
            // $cartItem = Cart::find($cartId);
            $cartItem = Cart::where('cartId', $cartId)->first();

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

    public function updateCartItem(Request $request, $cartId)
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

            // Retrieve the cart item
            $cartItem = Cart::where('cartId', $cartId)->first();

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

            // Fetch the product details using the cart item's productId
            $product = Product::where('productId', $cartItem->productId)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found.',
                ], 404);
            }
            // Validate the request data
            $request->validate([
                'new_quantity' => 'required|integer|min:1', // Minimum quantity is 1
            ]);

            $quantity = $request['new_quantity'];

            // Check if the requested quantity is available in stock
            if ($quantity > $product->quantityin_stock) {
                return response()->json([
                    'status' => false,
                    'message' => 'Requested quantity exceeds available stock.',
                ], 400);
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


    //Paystack payment integration
    /* private $initialize_url = "https://api.paystack.co/transaction/initialize";

    public function initialize_paystack(Request $request)

        {


            // Validate the request data
    $request->validate([
        'paymentMethod' => 'required|string|min:1', // Minimum quantity is 1
    ]);

    $paymentMethod = $request->input('paymentMethod');

    // Handle payment method validation
    // Example:
    if ($paymentMethod !== 'paystack') {
        return response()->json([
            'status' => false,
            'message' => 'Invalid payment method.',
        ], 400);
    }

    // Proceed with payment initialization
   
 
            // $amount = number_format($request->amount,2);
            $data = [
                //'email' => Auth::user()->email,
                'amount' => $request->amount * 100,
                'email' => 'yrryrjrthtu@yahoo.com'
    
            ];
            $fields_string = http_build_query($data);
            //open connection
            $ch = curl_init();
            //set the url, number of POST vars, POST data
    
            curl_setopt($ch,CURLOPT_URL, $this->initialize_url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer ".env('PAYSTACK_SECRET_KEY'),
    
            "Cache-Control: no-cache",
    
            ));
    
            //So that curl_exec returns the contents of the cURL; rather than echoing it
    
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
            //execute post
    
            $result = curl_exec($ch);
            $response = json_decode($result);
    
            return json_encode([
                    'data' => $response,
                    'metadata' => [
                        'payment_for' => 'token'
                    ]
               ]);
        }*/



    //Check out Function

    public function checkout(Request $request, $buyerId)
    {
        $orderId = rand(1000000000, 9999999999);

        try {
            // Retrieve the authenticated user's ID
            $authenticatedBuyerId = Auth::user()->buyerId;

            // Ensure that the user is logged in and matches the requested buyer ID
            if (!$authenticatedBuyerId || $authenticatedBuyerId != $buyerId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Buyer not authenticated or mismatched buyer ID.',
                ], 401);
            }

            $request->validate([
                'paymentMethod' => 'required|string|min:1', // Minimum quantity is 1
            ]);
            // Retrieve the cart items for the authenticated buyer
            $cartItems = Cart::where('buyerId', $buyerId)->get();

            // Check if cart items are retrieved successfully and if the cart is not empty
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty. Please add items to the cart first.',
                ], 400);
            }


            $paymentMethod = $request->input('paymentMethod');

            // Proceed with payment initialization based on the selected payment method
            if ($paymentMethod === 'paystack') {
                // Initialize payment using Paystack
                $initializeResponse = $this->initialize_paystack($cartItems);
            } elseif ($paymentMethod === 'paypal') {
                // Initialize payment using PayPal (you would implement this method)
                $initializeResponse = $this->initialize_paypal($cartItems);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payment method selected.',
                ], 400);
            }

            // Check if payment was successfully initialized

             return $initializeResponse;
            $paymentData = json_decode($initializeResponse);
            if ($paymentData && isset($paymentData->status) && $paymentData->status === true) {
                // Payment was successfully initialized, proceed with creating the order
                // Retrieve total amount
                $totalAmount = $cartItems->sum('total_price');
                if ($paymentData->data->status === 'true') {
                    // Iterate through each cart item and create an order for it
                    foreach ($cartItems as $cartItem) {
                        $order = new Order;
                        $order->buyerId = $buyerId;
                        $order->productId = $cartItem->productId;
                        $order->orderId = $orderId;
                        $order->productName = $cartItem->product_name;
                        $order->productImage = $cartItem->product_image;
                        $order->amount = $cartItem->selling_price;
                        $order->quantity = $cartItem->quantity;
                        $order->paymentMethod = $paymentMethod;
                        $order->Discount = null;
                        $order->shippingFee = null;
                        $order->order_status = 1;
                        $order->grand_price = $totalAmount;
                        $order->save();

                        // Update product quantity in stock and quantity sold
                        $product = Product::where('productId', $cartItem->productId)->first();
                        if ($product) {
                            $product->quantityin_stock -= $cartItem->quantity;
                            $product->quantity_sold += $cartItem->quantity;
                            $product->save();
                        }
                    }

                    // Clear the cart after successful checkout
                    Cart::where('buyerId', $buyerId)->delete();


                    // Notify the user via email about the successful payment
                    // Mail::to(Auth::user()->email)->send(new OrderConfirmationMail($order, $order->orderId,$order->amount));
                    //Mail::to(Auth::user()->email)->send(new OrderConfirmationMail($order, $order->orderId,$order->amount));



                    return response()->json([
                        'status' => true,
                        'message' => 'Checkout successful. Your order has been placed.',
                        'order' => $order, // You can also return the order details if needed
                        'paymentinfo' => $initializeResponse
                    ], 200);
                } else {
                    // Handle unsuccessful payment initialization
                    return response()->json([
                        'status' => false,
                        'message' => 'Payment initialization failed: ' . $paymentData->data->message,
                    ], 400);
                }
            } else {
                // Payment initialization failed or was not successful
                return response()->json([
                    'status' => false,
                    'message' => 'Payment initialization failed. Please try again later.',
                ], 400);
            }
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during checkout: ' . $e->getMessage(),
            ], 500);
        }
    }


    private $initialize_url = "https://api.paystack.co/transaction/initialize";

    public function initialize_paystack($cartItems)
    {
        $totalPrice = $cartItems->sum('total_price');

        // $amount = number_format($request->amount,2);
        $data = [
            'email' => Auth::user()->email,
            'amount' => $totalPrice * 100,
            //'email' => 'yrryrjrthtu@yahoo.com'

        ];
        $fields_string = http_build_query($data);
        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data

        curl_setopt($ch, CURLOPT_URL, $this->initialize_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),

            "Cache-Control: no-cache",

        ));

        //So that curl_exec returns the contents of the cURL; rather than echoing it

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //execute post

        $result = curl_exec($ch);
        $response = json_decode($result);

        return json_encode([
            'data' => $response,
            'metadata' => [
                'payment_for' => 'token'
            ]
        ]);
    }

    /* public function pay()
    {
        return view('payment.pay');
    }
    public function make_payment()
    {
        $formData = [
            'email' => request('email'),
            'amount' => request('amount') * 100,
            'callback_url' => route('pay.callback')
        ];
        $pay = json_decode($this->initiate_payment($formData));
        if ($pay) {
            if ($pay->status) {
                return redirect($pay->data->authorization_url);
            } else {
                return back()->withError($pay->message);
            }
        } else {
            return back()->withError("Something went wrong");
        }
    }

    public function payment_callback()
    {
        $response = json_decode($this->verify_payment(request('reference')));
        if ($response) {
            if ($response->status) {
                $data = $response->data;
                return view('pay.callback_page')->with(compact(['data']));
            } else {
                return back()->withError($response->message);
            }
        } else {
            return back()->withError("Something went wrong");
        }
    }

    public function initiate_payment($formData)
    {
        $url = "https://api.paystack.co/transaction/initialize";

        $fields_string = http_build_query($formData);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
            "Cache-Control: no-cache",
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function verify_payment($reference)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return  $response;
    }*/
}
