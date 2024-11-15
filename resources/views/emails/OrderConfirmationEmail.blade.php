<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>





</head>

<body>
    <div class="">
        <div class="bok"><img src="https://agroease.ng/assets/img/agroease1 2.png"></div>
        <div class="card">
            <div class="main-card">
                <div class="text-area">
                    <p style="color: #828282;  font-weight: 400;" class="f-20">Dear {{$orders[0]->firstname}},</p>
                    <p style=" font-weight: 600" class="f-20"> Thank you for your purchase on
                        <strong>Agroease!</strong> <br><br>
                        We are delighted
                         to confirm that your order has been successfully placed. <br> Below are the details of your order for your reference:
                    </p>


                    <div class="items-ordered">
                        <h7>
                            <strong>Items Ordered:</strong>
                        </h7>


                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">PRODUCT</th>
                                    <th scope="col">PRICE</th>
                                    <th scope="col">QUANTITY</th>
                                    <th scope="col">SUBTOTAL</th>
                                </tr>
                            </thead>
                            @php
                            $subtotal = 0; // Initialize subtotal
                            @endphp

                            @foreach ($orders as $index => $order)
                            <tbody>
                                <tr>

                                    <td><img src="{{  asset ('https://agroease.ng/agroease-api/public/uploads/product_images/'.explode(',',$order->productImage)[0]) }}" alt="Product Image" width="42px" height="41px"> {{$order->productName}}</td>
                                    <td>
                                        <p class="top">{{ '$' . number_format($order->amount, 2) }}</p>
                                    </td>
                                    <td>
                                        <p class="top">x{{$order->quantity}}</p>
                                    </td>
                                    <td> 
                                        <p class="top">{{ '$' . number_format($order->amount * $order->quantity, 2) }}</p>
                                    </td>
                                </tr>
                                @php
                                $subtotal += $order->amount * $order->quantity; // Add each item's subtotal to the total subtotal
                                @endphp
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                    <div class="order-summary">
                        <h7><br>
                            <strong>Order Summary:</strong>
                        </h7>
                        <ul>
                            <li>Order Number: {{$order->orderId}}</li>
                            <li>Order Date: {{$order->created_at}}</li>
                            <li>Shipping Address: {{$order->shipping_address}}</li>
                        </ul>
                    </div>


                    <div class="payment-information">
                        <h7>
                            <strong>Payment Information:</strong>
                        </h7>
                        <ul>
                            <li>Subtotal: {{ '$' . number_format($subtotal, 2) }}</li>
                            <li>Shipping Fee: {{ '$' . number_format($order->shippingFee, 2) }}</li>
                            <li>Total: {{ '$' . number_format($order->grand_price, 2) }}</li>
                            <li>Payment Method: {{$order->channel}} | {{$order->paymentMethod}}</li>
                        </ul>
                    </div>
                    <div class="tracking-info">
                        <h7><strong>Tracking Information:</strong></h7>
                        <div class="tracking-img">
                            <img src="https://agroease.ng/agroease-api/resources/views/emails/img/tracking-info.png" alt="">
                        </div>
                        <p>You can track your order using the following link: [Tracking Link]</p>
                        
                    </div>
                    <div class="assistance">
                        <h7><strong>Need Assistance?</strong></h7>
                        <p class="assist">Need Assistance? If you have any questions or require assistance regarding the sale <br> or shipping process, please don't hesitate to reach out to our support team at <br>
                            <span class="green"><strong><a href="support@agroease.ng">support@agroease.com</strong></span></a> or call us at <span class="green"><strong>+234 807 314 0444</strong></span>. <br> We're here to help you succeed!
                        </p>
                    </div>
                    <div class="regards">
                        <p>Best regards,</p>
                        <p>The AgroEase Team</p>

                    </div>

                </div>
            </div>

        </div>
        <div class="boks">
            <img src="https://agroease.ng/assets/img/agroease1.png">
            <p style="color: #828282;  font-weight: 400;" class="f-22">Copyright &copy; 2024. All Rights Reserved</p>
            <p style="color: #828282;  font-weight: 400;" class="f-21">Powered by</p>
        </div>
    </div>
</body>

</html>