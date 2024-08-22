<?php

namespace App\Http\Controllers;


use App\Models\Cart;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Seller;
use App\Models\Product;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use App\Mail\productSoldEmail;
use App\Mail\OrderConfirmationMail;
use App\Mail\SaleConfirmationEmail;
//use Unicodeveloper\Paystack\Paystack;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Unicodeveloper\Paystack\Facades\Paystack;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Paystack;



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
            $existingCartItem = Cart::where('buyerId', $buyerId)
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
                $existingCartItem->productWeight += $data['quantity'] * $product->productWeight; // Increment the total weight
                $existingCartItem->save();
                $cart = $existingCartItem; // Return the updated cart item
                //}

            } else {

                // Create a new Cart instance and populate it
                $cart = new Cart;
                $cart->cartId = $cartId;
                $cart->buyerId = $buyerId;
                $cart->buyerType = $buyerType;
                $cart->productId = $productId;
                $cart->product_image = $product->product_image;
                $cart->product_name = $product->product_name;
                $cart->product_category = $product->product_category;
                $cart->selling_price = $product->selling_price;
                $cart->productWeight = $data['quantity'] * $product->productWeight; // Set the initial total weight
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

    public function viewCart(Request $request)
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

            // Retrieve the cart items for the authenticated user
            $cartItems = Cart::where('buyerId', $buyerId)
                ->orderByDesc('id')
                ->get();
            // ->get();

            $products_in_cart_count = Cart::where('buyerId', $buyerId)->count();
            //$product_quantity__in_cart = Cart::where('user_id', $user->quantity)->count();

            $totalPrice = 0; // Initialize total price variable
            $totalQuantity = 0; // Initialize total quantity variable
            $totalWeight = 0; // Initialize total weight variable
            $feePerKg = 100; // Define the fee per kg

            // Calculate total price, total quantity, and total weight
            foreach ($cartItems as $item) {
                $totalQuantity += $item->quantity;
                $totalPrice += $item->selling_price * $item->quantity;
                $totalWeight += $item->productWeight;

                $product = Product::find($item->productId);
                $item->product_image_url = asset('uploads/product_images/' . $item->product_image);
            }

            // Calculate total shipping fee
            $totalShippingFee = $totalWeight * $feePerKg;




            // Return the cart items
            return response()->json([
                'status' => true,
                'message' => 'Cart items retrieved successfully.',
                'cart_items' => $cartItems,
                'products_in_cart' => $products_in_cart_count,
                'total_price' => $totalPrice,
                'total_quantity' => $totalQuantity,
                'total_weight' => $totalWeight,
                'total_shipping_fee' => $totalShippingFee
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving cart items: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function getCartQuantity(Request $request)
    {
        try {
            // Retrieve the authenticated user's ID
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

            // Retrieve the quantity of items in the cart for the authenticated user
            $cartQuantity = Cart::where('buyerId', $buyerId)->sum('quantity');

            // Return the cart quantity
            return response()->json([
                'status' => true,
                'message' => 'Cart quantity retrieved successfully.',
                'total_quantity' => $cartQuantity,
            ], 200);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving cart quantity: ' . $e->getMessage(),
            ], 500);
        }
    }


    //Delete cart Item

    public function deleteCartItem(Request $request, $cartId)
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
            if ($cartItem->buyerId !== $buyerId) {
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
            if ($cartItem->buyerId !== $buyerId) {
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
            $cartItem->productWeight = $quantity * $product->productWeight; // Increment the total weight
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


    //Check out Function

    public function confirmOrder(Request $request, $buyerId)
    {

        try {
            // Retrieve the authenticated user's ID
            $authenticatedBuyerId = Auth::user();

            // Ensure that the user is logged in and matches the requested buyer ID
            if (!$authenticatedBuyerId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Buyer not authenticated or mismatched buyer ID.',
                ], 401);
            }


            // Check if the user is an individual buyer
            $individualBuyer = Buyer::where('buyerId', $authenticatedBuyerId->buyerId)->first();
            $companyBuyer = CompanyBuyer::where('companyBuyerId', $authenticatedBuyerId->companyBuyerId)->first();


            
            // Check which buyer type is authenticated and validate the buyer ID
            if ($individualBuyer && $individualBuyer->buyerId == $buyerId) {
                $buyerType = 'individual';
                $buyerFirstName = $individualBuyer->firstname;
                $buyerLastName = $individualBuyer->lastname;
                $billing_address = $individualBuyer->city . ', ' . $individualBuyer->state . ', ' . $individualBuyer->country . ', ' . $individualBuyer->zipcode;
                $phone_number = $individualBuyer->phone_number;
                $email = $individualBuyer->email;
            } elseif ($companyBuyer && $companyBuyer->companyBuyerId == $buyerId) {
                $buyerType = 'company';
                $buyerFirstName = $companyBuyer->companyname; // Assuming company buyer has a companyName field
                $buyerLastName = ''; // Company buyers might not have a last name
                $billing_address = $companyBuyer->city . ', ' . $companyBuyer->state . ', ' . $companyBuyer->country . ', ' . $companyBuyer->zipcode;
                $phone_number = $companyBuyer->companyphone;
                $email = $companyBuyer->companyemail;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Buyer ID does not match the authenticated buyer.',
                ], 401);
            }




            /* $buyer = Buyer::where('buyerId', $buyerId)->first();
            $buyerFirstName = $buyer->firstname;
            $buyerLastName = $buyer->lastname;
            $billing_address = $buyer->city . ', ' . $buyer->state . ', ' . $buyer->country . ', ' . $buyer->zipcode;
            $phone_number = $buyer->phone_number;*/

            $request->validate([
                'paymentMethod' => 'required|string|min:1', // Minimum quantity is 1
                'shipping_address' => 'required|string|min:1',
                'city' => 'required|string|min:1',
                'state' => 'required|string|min:1',
                'phone_number' => 'required|string|min:1',

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


            // Calculate the shipping fee
            $shippingFee = 0;
            $feePerKg = 100;
            foreach ($cartItems as $cartItem) {
                $weight = $cartItem->productWeight;
                $quantity = $cartItem->quantity;
                $shippingFee += $weight  * $feePerKg;
            }

            $paymentMethod = $request->input('paymentMethod');
            $shippingData = $request->only(['shipping_address', 'city', 'state']);
            $shipping_address = $shippingData['shipping_address'];
            $city = $shippingData['city'];
            $state = $shippingData['state'];
            $phone_number = $request->input('phone_number');



            // Proceed with payment initialization based on the selected payment method
            if ($paymentMethod === 'paystack') {
                // Initialize payment using Paystack
                $initializeResponse = $this->initialize_paystack($cartItems, $paymentMethod, $buyerId, $shipping_address, $shippingFee, $buyerFirstName, $buyerLastName, $billing_address, $phone_number, $email);
            } elseif ($paymentMethod === 'flutterwave') {
                // Initialize payment using PayPal (you would implement this method)
                $initializeResponse = $this->initialize_payOnDelivery($cartItems, $paymentMethod, $buyerId, $shipping_address, $buyerFirstName, $buyerLastName, $billing_address);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payment method selected.',
                ], 400);
            }


            //return redirect($initializeResponse->data->authorization_url);

            return $initializeResponse;
            //dd($initializeResponse);
        } catch (\Exception $e) {
            // Return the error message in the response
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during checkout: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function paymentCallback(Request $request)
{
    $paymentDetails = Paystack::getPaymentData();

    try {
        $buyerId = $paymentDetails['data']['metadata']['buyerId'];
        $shippingFee = $paymentDetails['data']['metadata']['shippingFee'];

        $reference = $request->input('reference');
        $paymentMethod = request('paymentMethod');
        $customer_email = $paymentDetails['data']['customer']['email'];
        $paymentInfo = $paymentDetails['data']['id'];

        $paymentResponse = $this->paymentSuccess($paymentInfo);

        $response = json_decode($this->verifyPayment($reference), true);

        if ($response && isset($response['status']) && $response['status']) {
            $data = $response['data'];

            if ($paymentDetails['data']['status'] === 'success') {
                $orderId = rand(1000000000, 9999999999);
                $cartItems = Cart::where('buyerId', $buyerId)->get();

                if ($cartItems->isEmpty()) {
                    return redirect()->route('cart')->withError('Cart is empty. Please add items to the cart first.');
                }

                $orders = []; 
                $totalAmount = $data['amount'] / 100;
                $grandPrice = $totalAmount;

                $sellerTotals = [];

                foreach ($cartItems as $cartItem) {
                    $product = Product::where('productId', $cartItem->productId)->first();

                    if ($product) {
                        $sellerId = $product->sellerId;
                        $itemTotal = $cartItem->selling_price * $cartItem->quantity;
                        $itemPlatformFee = $itemTotal * 0.08;
                        $itemAccruedProfit = $itemTotal - $itemPlatformFee;

                        // Check if the seller is an individual or a company
                        $isSeller = Seller::where('sellerId', $product->sellerId)->first();
                        if ($isSeller) {
                            // Update individual seller's accrued profit and platform fee
                            $currentAccruedProfit = floatval($isSeller->accrued_profit);
                            $currentPlatformFee = floatval($isSeller->platform_fee);

                            $isSeller->accrued_profit = $currentAccruedProfit + $itemAccruedProfit;
                            $isSeller->platform_fee = $currentPlatformFee + $itemPlatformFee;
                            $isSeller->save();

                            // Fetch seller details
                            $sellerFirstName = $isSeller->firstname;
                            $sellerLastName = $isSeller->lastname;
                            $sellerFullName = $sellerFirstName . ' ' . $sellerLastName;
                            $sellerEmail = $isSeller->email;
                            $sellerPhone = $isSeller->phone;

                            $sellerDetails[$isSeller->sellerId] = [
                                'firstname' => $sellerFirstName,
                                'email' => $sellerEmail,
                                'phone' => $sellerPhone,
                                'is_company' => false,
                            ];
                        } else {
                            $companySeller = CompanySeller::where('companySellerId', $product->sellerId)->first();
                            if ($companySeller) {
                                // Update company seller's accrued profit and platform fee
                                $companySeller->accrued_profit += $itemAccruedProfit;
                                $companySeller->platform_fee += $itemPlatformFee;
                                $companySeller->save();

                                // Fetch seller details
                                $sellerFirstName = $companySeller->companyname;
                                $sellerEmail = $companySeller->companyemail;
                                $sellerPhone = $companySeller->companyphone;

                                $sellerDetails[$companySeller->sellerId] = [
                                    'firstname' => $sellerFirstName,
                                    'email' => $sellerEmail,
                                    'phone' => $sellerPhone,
                                    'is_company' => true,
                                ];
                            }
                        }

                        $product->quantityin_stock -= $cartItem->quantity;
                        $product->quantity_sold += $cartItem->quantity;
                        $product->save();
                    }

                    $order = new Order();
                    $order->buyerId = $buyerId;
                    $order->productId = $cartItem->productId;
                    $order->orderId = $orderId;
                    $order->productName = $cartItem->product_name;
                    $order->productImage = $cartItem->product_image;
                    $order->amount = $cartItem->selling_price;
                    $order->quantity = $cartItem->quantity;
                    $order->paymentMethod = $paymentDetails['data']['metadata']['paymentMethod'];
                    $order->paymentReference = $reference;
                    $order->Discount = null;
                    $order->shippingFee = $paymentDetails['data']['metadata']['shippingFee'];
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
                    $order->phone_number = $paymentDetails['data']['metadata']['phone_number'];
                    $order->grand_price = $grandPrice;
                    $order->sellerId = $product->sellerId;
                    $order->sellerFullname = $sellerFirstName;
                    $order->sellerEmail = $sellerEmail;
                    $order->sellerPhone = $sellerPhone;
                    $order->save();
                    $orders[] = $order;
                }

                Cart::where('buyerId', $buyerId)->delete();

                $adminEmail1 = 'hyacinth@agroease.ng';

                Mail::to($customer_email)->send(new OrderConfirmationMail($orders));
                Mail::to($adminEmail1)->send(new SaleConfirmationEmail($orders));

                return view('payment.callback-successful')->with(compact('data'));
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment was not successful' . $data['message'],
                ], 500);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong' . $e->getMessage(),
            ], 500);
        }
    } catch (\Exception $e) {
        \Log::error('An error occurred during payment callback: ' . $e->getMessage());
        return response()->json([
            'status' => false,
            'message' => 'An error occurred during payment callback' . $e->getMessage(),
        ], 500);
    }
}





    private $initialize_url = "https://api.paystack.co/transaction/initialize";

    public function initialize_paystack($cartItems, $paymentMethod, $buyerId, $shipping_address, $shippingFee, $buyerFirstName, $buyerLastName, $billing_address, $phone_number, $email)
    {
        //$secretKey = 'sk_live_bbc1ffec498f8a1342de5e8a9eac582cb4f83367';
        $secretKey = env('PAYSTACK_SECRET_KEY');
        //$product_name = $cartItems->product_name;
        $totalPrice = $cartItems->sum('total_price') + $shippingFee;
        // $paymentMethod = request($paymentMethod);

        // $amount = number_format($request->amount,2);

        $metadata = [
            'paymentMethod' => $paymentMethod,
            'buyerId' => $buyerId,
            'shipping_address' => $shipping_address,
            'billing_address' => $billing_address,
            'phone_number' => $phone_number,
            'firstname' => $buyerFirstName,
            'lastname' => $buyerLastName,
            'shippingFee' => $shippingFee,
        ];
        $data = array(
            'email' => $email,
            'amount' => $totalPrice * 100, // Convert to kobo
            'currency' => 'NGN',
            'metadata' => json_encode($metadata),
            'callback_url' => route('pay.callback'), // This is the route where the Paystack webhook will be sent
        );

        $fields_string = http_build_query($data);
        //open connection
        $ch = curl_init();
        //set the url, number of POST vars, POST data

        curl_setopt($ch, CURLOPT_URL, $this->initialize_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer " . $secretKey,

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
                'buyerId' => $buyerId,
                'shippingFee' => $shippingFee
            ]
        ]);
    }


    public function verifyPayment($reference)
    {

        $secretKey = env('PAYSTACK_SECRET_KEY');
        //$secretKey = 'sk_live_bbc1ffec498f8a1342de5e8a9eac582cb4f83367';

        $maxAttempts = 5;
        $delay = 2; // 2 seconds delay between attempts

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {

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
                    //"Authorization: Bearer " .$secretKey,
                    "Authorization: Bearer " . env('PAYSTACK_SECRET_KEY'),
                    "Cache-Control: no-cache",

                    // "X-Buyer-Id: $buyerId"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                error_log("cURL Error (Attempt $attempt): " . $err);
            } else {
                $decodedResponse = json_decode($response, true);
                if (isset($decodedResponse['status']) && $decodedResponse['status'] === true) {
                    error_log("Paystack Response (Attempt $attempt): " . $response);
                    return $response;
                }
                error_log("Paystack Response (Attempt $attempt, Verification failed): " . $response);
            }

            if ($attempt < $maxAttempts) {
                error_log("Waiting $delay seconds before next attempt...");
                sleep($delay);
            }
        }

        return json_encode(['status' => false, 'message' => 'Verification failed after ' . $maxAttempts . ' attempts']);
    }

    public function paymentSuccess($paymentInfo)
    {
        // Return the provided payment details
        // $paymentDetails = ['data']['status'];
        /* return response()->json([
        'payment Info' => $paymentInfo
    ]);*/

        //$secretKey = 'sk_live_bbc1ffec498f8a1342de5e8a9eac582cb4f83367';
        $secretKey = env('PAYSTACK_SECRET_KEY');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            //CURLOPT_URL => "https://api.paystack.co/transaction/verify/$paymentInfo",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . $secretKey,
                "Cache-Control: no-cache",

                // "X-Buyer-Id: $buyerId"
            ),
        ));

        $paymentResponse = curl_exec($curl);
        curl_close($curl);

        return  $paymentResponse;
    }


    public function getOrders()
    {

        $buyer = Auth::user();

        // Ensure the user is logged in
        if (!$buyer) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

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
            $product = Product::where('productId', $order->productId)->first();

            // Extract the first image URL for the product
            $productImage = null;
            if ($product && !empty($product->product_image)) {
                $images = explode(',', $product->product_image);
                if (!empty($images[0])) {
                    $productImage = asset('uploads/product_images/' . $images[0]);
                }
            }

            // Add the first image URL to the order object
            $order->product_image = $productImage;

            $order->total_price_per_item = $order->quantity * $order->grand_price;
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
        //$buyerId = Auth::user()->buyerId;

        $buyer = Auth::user();

        // Ensure the user is logged in
        if (!$buyer) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

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

        // Retrieve all orders associated with the authenticated user and the given order ID, ordered by most recent
        $orders = Order::where('buyerId', $buyerId)
            ->where('orderId', $orderId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Check if any orders exist
        if ($orders->isEmpty()) {
            return response()->json([
                'message' => 'No orders found for the authenticated user with the given order ID.',
            ], 404);
        }

        $subtotal = 0;
        $shippingFee = 0;

        // Map orders to include the product image URL and calculate prices
        $orders = $orders->map(function ($order) use (&$subtotal, &$shippingFee) {
            // Assuming the product image is stored in a column called 'productImage' in the orders table
            $productImage = null;
            if (!empty($order->productImage)) {
                $images = explode(',', $order->productImage);
                if (!empty($images[0])) {
                    $productImage = asset('uploads/product_images/' . $images[0]);
                }
            }
            $order->product_image_url = $productImage;

            // Calculate the price for each item
            $order->price_per_item = $order->amount; // Assuming grand_price is the price per item
            $order->total_price_per_item = $order->quantity * $order->price_per_item;

            // Add to subtotal
            $subtotal += $order->total_price_per_item;

            // Store shipping fee (assuming it's in a column called 'shippingFee')
            // We'll only use the shipping fee from the first item since it's calculated once for all items
            if ($shippingFee == 0) {
                $shippingFee = $order->shippingFee ?? 0;
            }

            return $order;
        });

        // Calculate total
        $total = $subtotal + $shippingFee;

        return response()->json([
            'message' => 'Orders fetched successfully.',
            'data' => [
                'orders' => $orders,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total' => $total
            ],
        ], 200);
    }
}
