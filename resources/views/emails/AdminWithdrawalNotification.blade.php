
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.cdnfonts.com/css/gilroy-bold?styles=20880" rel="stylesheet">
                
                
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

                                        <img src="agroease1 2.png" id="agro" style="height: 80px; margin-right: 340px; margin-bottom: 0px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <br>
                                    </td>
                                </tr>
                                <tr>

                                    <td style="padding:0 35px; ">
                                        <br>
                                        <br>
                                        <h1 style="color:#828282; font-weight:300; margin:0;font-size:15px;font-family:'Gilroy',sans-serif !important; text-align:start;  ">Dear Admin, </h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:530; margin-top:10px;font-size:18px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px; letter-spacing: normal; ">A seller has requested a withdrawal from their account. Below are the details of the withdrawal request and the seller's account information:</h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><strong>Seller Information:</strong> <br><br>
                                            <li>Seller Name: {{ $sellerDetails['fullname'] }}</li><br>
                                            <li>Email: {{ $sellerDetails['email'] }}</li> <br></h1><br>

                                            <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><strong>Withdrawal Details:</strong> <br><br>
                                                <li>Amount:  ₦{{ number_format($withdrawal->amount, 2) }}</li><br>
                                                <li>Date: {{ $withdrawal->initiated_at->format('Y-m-d H:i:s') }}</li><br>
                                                <li>Transaction ID:{{ $withdrawal->withdrawal_id }}</li> <br></h1><br>

                                                <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><strong>Account Details:</strong> <br><br>
                                                    <li>Account Holder Name: {{ $sellerDetails['account_name'] }}</li><br>
                                                    <li>Bank Name: {{ $sellerDetails['bank_name'] }}</li><br>
                                                    <li>Account Number:{{ $sellerDetails['account_number'] }}</li> <br></h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px">Please review the details and take the necessary steps to process the withdrawal.<br><br>Thank you<br><br>
                                            Best regards,<br><br>
                                            The Agroease Team</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="height:100px; width : 100%; background-color:#ddf8f6">

                                        <br>
                                        <h1 style="color:#828282;padding:0 15px; margin-right:20px;font-weight:400; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;vertical-align:start; line-height:2">Powered by</h1>
                                        <img src="agroease1.png" style="height: 80px; margin-bottom: 0px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;

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