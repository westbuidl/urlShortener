<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
//use Illuminate\Validation\Validator;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\PasswordResetEmail;
use App\Mail\sellerSignupEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    //
     //function for user registration
     public function signup(Request $request)
     {
         $sellerId = 'AGS' . rand(00000000, 99999999);
         $verification_code = rand(000000, 999999);
         
         $validator = Validator::make($request->all(), [
             'firstname' => 'required|min:2|max:100',
             'lastname' => 'required|min:2|max:100',
             'email' => 'required|email|unique:buyers',
             'phone' => 'required|min:2|max:100|unique:buyers',
             'product' => 'min:2|max:100',
             //'profile' => 'required|min:2|max:100',
             'country' => 'required|min:2|max:100',
             'state' => 'required|min:2|max:100',
             'city' => 'required|min:2|max:100',
             'zipcode' => '',
             'password' => 'required|min:6|max:100',
             'confirm_password' => 'required|same:password'
 
         ]);
         if ($validator->fails()) {
             return response()->json([
                 'message' => 'Validations fails',
                 'error' => $validator->errors()
             ], 422);
         }
 
         $seller = Seller::create([
             'sellerId' => $sellerId,
             'firstname' => $request->firstname,
             'lastname' => $request->lastname,
             'email' => $request->email,
             'phone' => $request->phone,
             'product' => $request->product,
             //'profile' => $request->profile,
             'country' => $request->country,
             'state' => $request->state,
             'city' => $request->city,
             'zipcode' => $request->zipcode,
             'password' => Hash::make($request->password),
             'verification_code' => $verification_code
 
 
 
         ]);
 
         Mail::to($request->email)->send(new sellerSignupEmail($seller));
 
 
         return response()->json([
             'message' => 'Registration successful Verification Email Sent',
             'data' => $seller
 
             /*'data' => [
                 'firstname' => $buyer->firstname,
                 'lastname' => $buyer->lastname,
                 'email' => $buyer->email
             ]*/
         ], 200);
     }
 
}
