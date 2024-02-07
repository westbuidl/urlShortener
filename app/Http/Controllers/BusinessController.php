<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Mail\SignupEmail;
use Illuminate\Support\Facades\Mail;



class BusinessController extends Controller
{
    //


    public function business(Request $request){

        $businessID = 'AGB-'.rand(000, 999);
        $verification_code = rand(000000, 999999);

        
        /* $request->validate([
             'firstname'=>'required|min:2|max:100',
             'lastname'=>'required|min:2|max:100',
             'email'=>'required|email|unique:users',
             'phone'=>'required|min:2|max:100',
             'product'=>'required|min:2|max:100',
             'country'=>'required|min:2|max:100',
             'state'=>'required|min:2|max:100',
             'city'=>'required|min:2|max:100',
             'zipcode'=>'required|min:2|max:100',
             'password'=>'required|min:6|max:100',
             'confirm_password'=>'required|same:password'
         ]);*/
        $validator = Validator::make($request->all(),[
             //'businessID'=>'required|min:2|max:100',
             'businessname'=>'required|min:2|max:100',
             'businessregnumber'=>'required|unique:business_accounts',
             'businessemail'=>'required|unique:business_accounts',
             'businessphone'=>'required|min:2|max:100|unique:business_accounts',
             'products'=>'required|min:2|max:100',
             'businessaddress'=>'required|min:2|max:100',
             'country'=>'required|min:2|max:100',
             'city'=>'required|min:2|max:100',
             'state'=>'required|min:2|max:100',
             'zipcode'=>'required|min:2|max:100',
             'password'=>'required|min:6|max:100',
             'confirm_password'=>'required|same:password'
 
         ]);
        if ($validator->fails()) {
         return response()->json([
             'message'=>'Business Registration failed',
             'error'=>$validator->errors()
         ],422);
     }
 
    $business=BusinessAccount::create([
                'businessID'=>$businessID,
                'businessname'=>$request->businessname,
                'businessregnumber'=>$request->businessregnumber,
                'businessemail'=>$request->businessemail,
                'businessphone'=>$request->businessphone,
                'products'=>$request->products,
                'businessaddress'=>$request->businessaddress,
                'country'=>$request->country,
                'city'=>$request->city,
                'state'=>$request->state,
                'zipcode'=>$request->zipcode,
                //'password'=>'required|min:6|max:100',
                'password'=>Hash::make($request->password),
                'verification_code' => $verification_code
             //'confirm_password'=>'required|same:password'
    ]);
    Mail::to($request->businessemail)->send(new SignupEmail($business));
     return response()->json([
         'message'=>'Registration successful Check email for verification code',
         'data'=>$business
     ],200);
     
     }


     public function businesslogin(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>'Login failed Email and Password required',
                'error'=>$validator->errors()
            ],422);
        }

        $business=BusinessAccount::where('businessemail',$request->email)->first();

        if($business){
        if ($business->email_verified_at) {
            if(Hash::check($request->password,$business->password)){
                $token=$business->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'message'=>'Login Successful',
                    'token'=>$token,
                    'data'=>$business
                ],200);

            }else{
                return response()->json([
                    'message'=>'Incorrect Credentials',
                ],400);

            }

        } else {
            return response()->json([
                'message' => 'Email not verified. Please verify your email first.',
            ], 400);
        }

        }else{
            return response()->json([
                'message'=>'Incorrect Credentials',
            ],400);

        }
     }


     //Email verification function

     public function verifyMailBusiness(Request $request)
{
    // Validate request inputs
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'otp' => 'required'
    ]);

    // If validation fails, return error response
    if ($validator->fails()) {
        return response()->json([
            'error' => $validator->errors()->first(),
        ], 400);
    }

    // Fetch email and OTP from the form
    $email = $request->input('email');
    $otp = $request->input('otp');
   
    // Find individual user with the provided email and OTP
    $business = BusinessAccount::where('businessemail', $email) ->where('verification_code', $otp)->first();

    // If individual user is found
    if ($business) {
        // Check if email is already verified
        if ($business->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        // Mark email as verified
        $business->email_verified_at = Carbon::now();
        $business->is_verified = true;
        $business->save();

        return response()->json([
            'message' => 'Email verified. Proceed to login.',
        ], 200);
    } else {
        // If individual user is not found or OTP is incorrect
        return response()->json([
            'message' => 'Invalid email or OTP. Please try again.',
        ], 400);
    }
}
}
