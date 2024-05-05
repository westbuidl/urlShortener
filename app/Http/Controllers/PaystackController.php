<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaystackController extends Controller
{
    //

    private $initialize_url = "https://api.paystack.co/transaction/initialize";

public function initialize_paystack(Request $request)
    {
        // $amount = number_format($request->amount,2);
        $fields = [
            'email' => $request->user()->email,
            'amount' => $request->amount * 100,
            //'email' => 'yrryrjrthtu@yahoo.com'

        ];
        $fields_string = http_build_query($fields);
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
    }
}
