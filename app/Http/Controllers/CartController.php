<?php

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Mail\OrderConfirmationMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
//use Unicodeveloper\Paystack\Paystack;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
//use Unicodeveloper\Paystack\Facades\Paystack;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Paystack;


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

    public function confirmOrder(Request $request, $buyerId)
    {

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


            $buyer = Buyer::where('buyerId', $buyerId)->first();
            $buyerFirstName = $buyer->firstname;
            $buyerLastName = $buyer->lastname;
            $billing_address = $buyer->city . ', ' . $buyer->state . ', ' . $buyer->country . ', ' . $buyer->zipcode;

            $request->validate([
                'paymentMethod' => 'required|string|min:1', // Minimum quantity is 1
                'shipping_address' => 'required|string|min:1',
                'city' => 'required|string|min:1',
                'state' => 'required|string|min:1',

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
            $shippingData = $request->only(['shipping_address', 'city', 'state']);
            $shipping_address = $shippingData['shipping_address'];
            $city = $shippingData['city'];
            $state = $shippingData['state'];

            // Proceed with payment initialization based on the selected payment method
            if ($paymentMethod === 'paystack') {
                // Initialize payment using Paystack
                $initializeResponse = $this->initialize_paystack($cartItems, $paymentMethod, $buyerId, $shipping_address, $buyerFirstName, $buyerLastName, $billing_address);
            } elseif ($paymentMethod === 'flutterwave') {
                // Initialize payment using PayPal (you would implement this method)
                $initializeResponse = $this->initialize_flutterwave($cartItems, $paymentMethod, $buyerId, $shipping_address, $buyerFirstName, $buyerLastName, $billing_address);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payment method selected.',
                ], 400);
            }


            //return redirect($initializeResponse->data->authorization_url);

            return $initializeResponse;
            dd($initializeResponse);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during checkout: ' . $e->getMessage(),
            ], 500);
        }
    }









    public function payment_callback(Request $request)

    {
        $paymentDetails = Paystack::getPaymentData();

        try {

            //dd(Auth::user());
            $buyerId = $paymentDetails['data']['metadata']['buyerId'];


            $reference = $request->input('reference');
            //$status = request('status');
            $paymentMethod = request('paymentMethod');
            $customer_email = $paymentDetails['data']['customer']['email'];
            //$buyerId = $request->input('buyerId');

            $response = json_decode($this->verify_payment($reference), true); // Decode as associative array for easier access


            if ($response && isset($response['status']) && $response['status']) {
                $data = $response['data'];
                //$ = $response['data'];

                // Check if payment was successful
                if ($paymentDetails['data']['status'] === 'success') {
                    // Payment was successful, proceed to create an order
                    $orderId = rand(1000000000, 9999999999);









                    // Retrieve the cart items for the authenticated buyer
                    $cartItems = Cart::where('buyerId', $buyerId)->get();

                    // Check if cart items are retrieved successfully and if the cart is not empty
                    if ($cartItems->isEmpty()) {
                        return redirect()->route('cart')->withError('Cart is empty. Please add items to the cart first.');
                    }

                    // Proceed with creating the order
                    $totalAmount = $data['amount'] / 100; // Convert amount back to actual value
                    foreach ($cartItems as $cartItem) {
                        $order = new Order();
                        $order->buyerId = $buyerId;
                        $order->productId = $cartItem->productId;
                        $order->orderId = $orderId;
                        $order->productName = $cartItem->product_name;
                        $order->productImage = $cartItem->product_image;
                        $order->amount = $cartItem->selling_price;
                        $order->quantity = $cartItem->quantity;
                        $order->paymentMethod = $paymentDetails['data']['metadata']['paymentMethod']; // Assuming paystack is used
                        $order->paymentReference = $reference;
                        $order->Discount = null;
                        $order->shippingFee = null;
                        $order->order_status = $paymentDetails['data']['status'];
                        $order->currency = $paymentDetails['data']['currency'];
                        $order->channel = $paymentDetails['data']['channel'];
                        $order->payment_id = $paymentDetails['data']['id'];
                        $order->country_code = $paymentDetails['data']['authorization']['country_code'];
                        $order->customer_email = $paymentDetails['data']['customer']['email'];
                        $order->shipping_address = $paymentDetails['data']['metadata']['shipping_address'];
                        $order->billing_address = $paymentDetails['data']['metadata']['billing_address'];
                        $order->firstname = $paymentDetails['data']['metadata']['firstname'];
                        $order->lastname = $paymentDetails['data']['metadata']['lastname'];
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

                    // Send verification email
                    Mail::to($customer_email)->send(new OrderConfirmationMail($order, $order->productName, $order->firstname, $order->lastname));
                    // dd($paymentDetails);


                    // Return success response
                    return view('payment.callback')->with(compact('data'));
                } else {
                    // Payment was not successful, handle accordingly

                    return response()->json([
                        'status' => false,
                        'message' => 'Payment was not successful' . $data['message'],
                    ], 500);
                    // return redirect()->route('confirmOrder')->withError('Payment was not successful: ' . $data['message']);
                }
            } else {
                // Error occurred or invalid response, handle accordingly
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong' . $e->getMessage(),
                ], 500);
                //return redirect()->route('confirmOrder')->withError('Something went wrong');
            }
        } catch (\Exception $e) {
            // Log the exception
            \Log::error('An error occurred during payment callback: ' . $e->getMessage());

            // Return error message
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during payment callback' . $e->getMessage(),
            ], 500);
        }
    }


    private $initialize_url = "https://api.paystack.co/transaction/initialize";

    public function initialize_paystack($cartItems, $paymentMethod, $buyerId, $shipping_address, $buyerFirstName, $buyerLastName, $billing_address)
    {
        //$product_name = $cartItems->product_name;
        $totalPrice = $cartItems->sum('total_price');
        // $paymentMethod = request($paymentMethod);

        // $amount = number_format($request->amount,2);

        $metadata = [
            'paymentMethod' =>  $paymentMethod,
            'buyerId' => $buyerId,
            'shipping_address' => $shipping_address,
            'billing_address' => $billing_address,
            'firstname' => $buyerFirstName,
            'lastname' => $buyerLastName,
        ];
        $data = array(
            'email' => Auth::user()->email,
            'amount' => $totalPrice * 100,
            'currency' => 'NGN',

            'metadata' => json_encode($metadata),

            'callback_url' => route('pay.callback'),

        );

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
                'payment_for' => 'product_name',
                'paymentMethod' => $paymentMethod,
                'buyerId' => $buyerId
            ]
        ]);
    }


    public function verify_payment($reference, $paymentMethod = [])
    {
        if ($paymentMethod === null) {
            $paymentMethod = [];
        }

        $paymentMethod = http_build_query($paymentMethod);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/$reference?$paymentMethod",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                "Cache-Control: no-cache",

                // "X-Buyer-Id: $buyerId"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return  $response;
    }


    public function getOrders()
    {
        // Retrieve the authenticated user's ID
        $buyerId = Auth::user()->buyerId;

        // Retrieve all orders associated with the authenticated user
        $orders = Order::where('buyerId', $buyerId)->orderByDesc('created_at')->get();

        // Check if any orders exist
        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders found for the authenticated user.',
            ], 404);
        }

        // Iterate through each order to fetch product details and image URLs
        foreach ($orders as $order) {
            // Retrieve product details for the order
            $products = Product::where('productId', $order->productId)->get();

            // Extract image URLs for each product
            $productImages = [];
            foreach ($products as $product) {
                if (!empty($product->product_image)) {
                    foreach (explode(',', $product->product_image) as $image) {
                        $productImages[] = asset('uploads/product_images/' . $image);
                    }
                }
            }

            // Add image URLs to the order object
            $order->product_images = $productImages;
        }

        return response()->json([
            'message' => 'All orders fetched successfully for the authenticated user.',
            'data' => [
                'orders' => $orders,
            ]
        ], 200);
    }


    public function getOrderById($orderId)
    {
        // Retrieve the authenticated user's ID
        $buyerId = Auth::user()->buyerId;

        // Retrieve the order associated with the authenticated user and the given order ID
        $order = Order::where('buyerId', $buyerId)
            ->where('orderId', $orderId)
            ->first();

        // Check if the order exists
        if (!$order) {
            return response()->json([
                'message' => 'Order not found for the authenticated user.',
            ], 404);
        }

        // Retrieve product details for the order
        $products = Product::where('productId', $order->productId)->get();

        // Extract image URLs for each product
        $productImages = [];
        foreach ($products as $product) {
            if (!empty($product->product_image)) {
                foreach (explode(',', $product->product_image) as $image) {
                    $productImages[] = asset('uploads/product_images/' . $image);
                }
            }
        }

        // Add image URLs to the order object
        $order->product_images = $productImages;

        return response()->json([
            'message' => 'Order fetched successfully for the authenticated user.',
            'data' => [
                'order' => $order,
            ]
        ], 200);
    }
}
