<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\IndividualAccount;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;
use App\Mail\SignupEmail;

class UserController extends Controller
{
    //function for user registration
    public function individual(Request $request){
        $userID = 'AGU-'.rand(00000000, 99999999);
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
            'firstname'=>'required|min:2|max:100',
            'lastname'=>'required|min:2|max:100',
            'email'=>'required|email|unique:individual_accounts',
            'phone'=>'required|min:2|max:100|unique:individual_accounts',
            'product'=>'required|min:2|max:100',
            'country'=>'required|min:2|max:100',
            'state'=>'required|min:2|max:100',
            'city'=>'required|min:2|max:100',
            'zipcode'=>'required|min:2|max:100',
            'password'=>'required|min:6|max:100',
            'confirm_password'=>'required|same:password'

        ]);
       if ($validator->fails()) {
        return response()->json([
            'message'=>'Validations fails',
            'error'=>$validator->errors()
        ],422);
    }

   $individualuser=IndividualAccount::create([
             'userID'=>$userID, 
            'firstname'=>$request->firstname,
            'lastname'=>$request->firstname,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'product'=>$request->product,
            'country'=>$request->country,
            'state'=>$request->state,
            'city'=>$request->city,
            'zipcode'=>$request->zipcode,
            'password'=>Hash::make($request->password),
             'verification_code' => $verification_code
            

            
   ]);
  
    Mail::to($request->email)->send(new SignupEmail($individualuser));

    return response()->json([
        'message'=>'Registration successful Verification Email Sent',
        'data'=>$individualuser
    ],200);
    
    }

//function for user login
    public function userlogin(Request $request){
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

        $individualuser=IndividualAccount::where('email',$request->email)->first();

        if($individualuser){
         if ($individualuser->email_verified_at) {
               if(Hash::check($request->password,$individualuser->password)){
                $token=$individualuser->createToken('auth-token')->plainTextToken;
                // Store user ID in session
                session(['userID' => $individualuser->userID]);

                return response()->json([
                    'message'=>'Login Successful',
                    'token'=>$token,
                    'data'=>$individualuser
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
     //function to fetch user data with bearer tokens
        public function individualuser(Request $request){
            return response()->json([
                'message'=>'User successfully fetched',
                'data'=>$request->individualuser()
            ],200);
        }

    //function to logout
        public function logout(Request $request){
            $request->individualuser()->currentAccessToken()->delete();
            // Remove user ID from session
             $request->session()->forget('userID');
            return response()->json([
                'message'=>'User logged out',

            ],200);
        }

        //function to check and verify email

       
public function verifymail(Request $request)
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
    $individualuser = IndividualAccount::where('email', $email) ->where('verification_code', $otp)->first();

    // If individual user is found
    if ($individualuser) {
        // Check if email is already verified
        if ($individualuser->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        // Mark email as verified
        $individualuser->email_verified_at = Carbon::now();
        $individualuser->is_verified = true;
        $individualuser->save();

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


