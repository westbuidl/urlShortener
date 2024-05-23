<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body marginheight="0" topmargin="0" marginwidth="0" style="margin: 0px; background-color: #ddf8f6;" leftmargin="0">
    <table cellspacing="0" border="0" cellpadding="0" width="100%" bgcolor="#ddf8f6" style="@import url(https://fonts.googleapis.com/css?family=Rubik:300,400,500,700|Open+Sans:300,400,600,700); font-family: 'Open Sans', sans-serif;">
        <tr>
            <td>
                <table style="max-width:670px;  margin:0 auto;" width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <br>
                            <table width="95%" border="0" align="center" cellpadding="0" cellspacing="0" style="max-width:570px;background:#fff; border-radius:3px; text-align:center;-webkit-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);-moz-box-shadow:0 6px 18px 0 rgba(0,0,0,.06);box-shadow:0 6px 18px 0 rgba(0,0,0,.06);">
                                <tr>
                                    <td style="height:100px; width : 100%; background-color:#1d988c">

                                        <img src="https://agroease.ng/assets/img/agroease1%202.png" id="agro" style="height: 80px; margin-right: 340px; margin-bottom: 0px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <!-- <img src="party-popper 1.png" style="height: 96px; margin-left: -79px; margin-top: -5px; position: absolute;"></i> -->
                                        <br>
                                    </td>
                                </tr>
                                <tr>

                                    <td style="padding:0 35px; ">
                                        <br>
                                        <br>
                                        <h1 style="color:#828282; font-weight:300; margin:0;font-size:15px;font-family:'Gilroy',sans-serif; text-align:start;  ">Dear {{$orders[0]->firstname}}, </h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:510; margin-top:10px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px; letter-spacing: normal; ">Congratulations! Your order has been successfully processed, and we're thrilled to confirm your purchase with us</h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px">Sold Product Details: <br><br>
                                        @foreach ($orders as $index => $order)
                                            <li><strong>{{ $index + 1 }}:</strong></li>
                                            <li>Product: <strong>{{$order->productName}}</strong></li><br>
                                            <li>Order Number: <strong>{{$order->orderId}}</strong></li><br>
                                            <li>Product ID: <strong>{{$order->productId}}</strong></li><br>
                                            <li>Quantity: <strong>{{$order->quantity}}</strong></li><br>
                                            <li>Unit Price: <strong>{{  '₦' . number_format($order->amount, 2) }}</strong></li><br>
                                            <li>Total Amount: <strong>{{  '₦' . number_format($order->amount * $order->quantity, 2) }}</strong></li><br>
                                            <li><br><img src="{{ asset('uploads/product_images/' . $order->productImage) }}" alt="{{$order->productName}}" style="width: 100px; height: auto;"></li><br>
                                        @endforeach
                                        </h1>
                                        <li>Grand Total: <strong>{{  '₦' . number_format($order->grand_price, 2) }}</strong></li><br>
                                        <br>

                                       
       
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px">Shipping Information: <br><br>
                                            <li>Shipping Address: <strong> {{$order->shipping_address}}</strong></li><br>
                                            <li>Shipping Method: <strong>[Shipping Method]</strong></li><br>
                                        </h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px">Payment Information: <br><br>
                                            <li>Payment Method: <strong>{{$order->channel}} | {{$order->paymentMethod}}</strong></li><br>
                                            <li>Billing Address: <strong>{{$order->billing_address}}</strong></li><br>
                                            <li>Date: <strong>{{$order->created_at}}</strong></li><br>
                                        </h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><br>We sincerely appreciate your business and trust in us. If there's anything else we can assist you with, please don't hesitate to let us know.
                                            <br><br>Thank you for your continued partnership and trust in Agroease.<br><br>Best regards, <br><br>The Agroease Team</h1><br>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:100px; width : 100%; background-color:#ddf8f6">

                                        <br>
                                        <h1 style="color:#828282;padding:0 15px; margin-right:20px;font-weight:400; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;vertical-align:start; line-height:2">Powered by</h1>
                                        <img src="https://agroease.ng/assets/img/agroease1.png" style="height: 80px; margin-bottom: 0px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;

                                        <h1 style="color:#828282;padding:0 15px; margin-right:20px;font-weight:400; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;vertical-align:start; line-height:2">Copyright © 2024 All rights reserved</h1>
                                        <br>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <tr>
                            <td style="height:20px;">&nbsp;</td>
                        </tr>
                </table>
            </td>
            </tr>
    </table>

</body>

</html>