<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
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
                                        <br>
                                    </td>
                                </tr>
                                <tr>

                                    <td style="padding:0 35px; ">
                                        <br>
                                        <br>
                                        <h1 style="color:#828282; font-weight:300; margin:0;font-size:15px;font-family:'Gilroy',sans-serif; text-align:start;  ">Dear  {{$companyBuyer->companyname}}, </h1><br>
                                        <h1 style="color:#1a1a1a; font-weight:510; margin-top:10px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px; letter-spacing: normal; ">We've received a request to reset the password for your <strong>Agroease</strong> account. To proceed with the password reset, please use the following verification code:</h1><br>
                                        <h1 style="background-color:#7e66ef; cursor:pointer; border-radius: 100px; height: 52px; padding:17px, 141px, 17px, 141px; margin-top:10px;"><span style="font-weight:600; color:#fff; font-size:26px;">{{$reset_password}}</span></h1><br>

                                        <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><strong>Why do we need this code?</strong> <br><br> This code ensures the security of your account and helps us verify your identity during the password reset process.
                                            <br>
                                            <br>
                                            <h1 style="color:#1a1a1a; font-weight:500; margin-top:2px;font-size:15px;font-family:'Gilroy',sans-serif;text-align:justify; line-height:22px"><strong>Important:</strong> <br><br>
                                                <li>Please do not share this code with anyone else.</li>
                                                <li>If you didn 't attempt to register for Agroease, please disregard this email.</li> <br><br>If you encounter any difficulties or have any questions, don't hesitate to reach out to our support team at
                                                <a href="mailto:support@agroease.ng" style="color: #1d988c; text-decoration: none;"><strong>support@agroease.ng</strong></a>
                                                <br><br>Thank you for choosing <strong>Agroease!</strong> We look forward to serving you. <br><br>Best regards, <br><br>The Agroease Team</h1><br>
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