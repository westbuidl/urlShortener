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
                                    <td style="height:100px; width : 100%; background-color:#7e66ef">

                                        <img src="agroease1 2.png" id="agro" style="height: 80px; margin-right: 340px; margin-bottom: 0px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;
                                        <br>
                                    </td>
                                </tr>
                                <tr>

                                    <td style="padding:0 35px; ">
                                        <br>
                                        <br>
                                        <h1 style="color:#828282; font-weight:300; margin:0;font-size:15px;font-family:'Gilroy',sans-serif !important; text-align:start;  ">Dear  {{$firstname}}, </h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:530; margin-top:10px;font-size:18px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px; letter-spacing: normal; ">We are pleased to inform you that your recent wallet withdrawal request has been successfully processed. Please find the details of your transaction below:</h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><strong>Withdrawal Details:</strong> <br>
                                        <li>Amount: ₦{{ number_format($amount, 2) }}</li><br>

                                            <li>Transaction ID: {{$withdrawalId}}</li></h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px">Your funds should appear in your Bank account shortly, depending on the processing time of your financial institution. If you experience any delays, feel free to contact us for assistance.<br><br><br>
                                            If you have any questions or concerns regarding this transaction, please reach out to our customer support team at <a href="mailto:contact@agroease.ng"
                                            style="color: #1d988c; text-decoration: none;"><strong>contact@agroease.ng</strong></a> or call us at <span style="color: #1d988c; font-weight: 600;">+234 807 314 0444</span>.<br><br>
                                            Thank you for choosing <strong>Agroease!</strong> We look forward to serving you.<br><br>
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