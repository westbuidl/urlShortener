<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Buyer;
use Illuminate\Support\Str;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use App\Mail\buyerSignupEmail;
use Illuminate\Support\Carbon;
use App\Mail\buyerEmailVerified;
use App\Mail\PasswordResetEmail;
use App\Mail\resendBuyerEmailAuth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\buyerPasswordResetEmail;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;

class BuyerController extends Controller
{
    //function for user registration
    public function signup(Request $request)
    {
        $buyerId = 'AGB' . rand(10000000, 99999999);
        $verification_code = rand(100000, 999999);

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:2|max:100',
            'lastname' => 'required|min:2|max:100',
            'email' => 'required|email|unique:buyers',
            'phone' => 'required|min:2|max:100|unique:buyers',
            //'product' => 'min:2|max:100',
            //'profile' => 'required|min:2|max:100',
            'country' => 'required|min:2|max:100',
            'state' => 'required|min:2|max:100',
            'city' => 'required|min:2|max:100',
            'address' => 'min:2|max:100',
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

        $buyer = Buyer::create([
            'buyerId' => $buyerId,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            //'product' => $request->product,
            //'profile' => $request->profile,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address,
            'zipcode' => $request->zipcode,
            'password' => Hash::make($request->password),
            'verification_code' => $verification_code



        ]);

        Mail::to($request->email)->send(new buyerSignupEmail($buyer, $buyer->firstname));


        return response()->json([
            'message' => 'Registration successful Verification Email Sent',
            'data' => $buyer

            /*'data' => [
                'firstname' => $buyer->firstname,
                'lastname' => $buyer->lastname,
                'email' => $buyer->email
            ]*/
        ], 200);
    }

    public function verifyBuyerEmail(Request $request)
    {
        // Validate request inputs
        $validator = Validator::make($request->all(), [
            //'email' => 'required|email',
            'otp' => 'required'
        ]);

        // If validation fails, return error response
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 400);
        }

        // Fetch email and OTP from the form
        //$email = $request->input('email');
        //$otp = $request->input('otp');

        // Find individual user with the provided email and OTP
        $buyer = Buyer::where('verification_code', $request->otp)->first();

        // If individual user is found
        if ($buyer) {
            // Check if email is already verified
            if ($buyer->email_verified_at) {

                return response()->json([
                    'message' => 'Email already verified.',
                ], 400);
            }

            // Mark email as verified
            $buyer->email_verified_at = Carbon::now();
            $buyer->is_verified = true;
            $buyer->save();

            Mail::to($buyer->email)->send(new buyerEmailVerified($buyer, $buyer->firstname));
            return response()->json([
                'message' => 'Email verified. Proceed to login.',
            ], 200);
        } else {
            // If individual user is not found or OTP is incorrect
            return response()->json([
                'message' => 'Invalid OTP. Please try again.',
            ], 400);
        }
    }




    //function for buyer login
    public function loginBuyer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Login failed Email and Password required',
                'error' => $validator->errors()
            ], 422);
        }

        $buyer = Buyer::where('email', $request->email)->first();

        if ($buyer) {
            if ($buyer->email_verified_at) {
                if (Hash::check($request->password, $buyer->password)) {
                    $token = $buyer->createToken('auth-token')->plainTextToken;
                    // Store user ID in session
                    session(['buyerId' => $buyer->buyerId]);
                    session(['email' => $buyer->email]);

                    //return Redirect::route('user.dashboard')->with('token', $token);

                    return response()->json([
                        'message' => 'Login Successful',
                        'token' => $token,
                        'data' => $buyer
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Incorrect Credentials',
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Email not verified. Please verify your email first.',
                ], 400);
            }

            // Check if the email exists in the CompanyBuyer model
    $companyBuyer = CompanyBuyer::where('companyemail', $request->email)->first();

    if ($companyBuyer) {
        if ($companyBuyer->email_verified_at) {
            if (Hash::check($request->password, $companyBuyer->password)) {
                $token = $companyBuyer->createToken('auth-token')->plainTextToken;
                // Store user ID in session
                session(['companyBuyerId' => $companyBuyer->companyBuyerId]);
                session(['email' => $companyBuyer->companyemail]);

                return response()->json([
                    'message' => 'Login Successful',
                    'token' => $token,
                    'data' => $companyBuyer,
                    'type' => 'company',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Incorrect Credentials',
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Email not verified. Please verify your email first.',
            ], 400);
        }
    }

        } else {
            return response()->json([
                'message' => 'Incorrect Credentials',
            ], 400);
        }
    }
    
    //function to fetch user data with bearer tokens
    public function buyer(Request $request)
    {
        return response()->json([
            'message' => 'User successfully fetched',
            'data' => $request->user()
        ], 200);
    }

   

    public function buyerLogout(Request $request)
{
    // Assuming 'BuyerId' is a field in the authenticated user model

    $buyer = $request->user();

    //$buyerId = $request->input('buyerId');
    
    // Find the buyer using the BuyerId
    //$buyer = Buyer::where('buyerId', $buyer->buyerId)->first();

    if ($buyer instanceof Buyer) {
        // Revoke all tokens for the buyer
        $buyer->tokens()->delete();

        return response()->json([
            'message' => 'Individual logged out successfully',
        ], 200);
    }elseif ($buyer instanceof CompanyBuyer){
            $buyer->tokens()->delete();
            return response()->json([
                'message' => 'Company buyer logged out successfully',
            ], 200);
    } else {
        return response()->json([
            'message' => 'Buyer not found',
        ], 404);
    }
}



    //function to check and verify email



    //function to reset password
    public function buyerPasswordReset(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:buyers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email not found.',
            ], 400);
        }

        //Generate new code for password
        $reset_password = rand(10000000, 99999999);

        $buyer = Buyer::where('email', $request->email)->first();
        // $buyer->password = $reset_password;
        $buyer->update([
            'password' => Hash::make($reset_password)
        ]);

        $buyer->save();

        Mail::to($buyer->email)->send(new buyerPasswordResetEmail($buyer, $reset_password, $buyer->firstname));
        return response()->json([
            'message' => 'Password reset code sent.',
            'password_data' => $reset_password
        ], 200);
    }





    //function to resend verification code
    public function resendBuyerEmailAuth(Request $request, $email)
    {
        // Retrieve the email from the request body
        // $email = $request->input('email');

        // Validate the email address format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Invalid email address. Please provide a valid email address.',
            ], 400);
        }

        // Retrieve the buyer by email from the database
        $buyer = Buyer::where('email', $email)->first();

        // Check if the buyer's email is already verified
        if ($buyer->is_verified) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ], 400);
        }

        // Check if buyer exists
        if (!$buyer) {
            return response()->json([
                'message' => 'User not found for the provided email address.',
            ], 404);
        }

        // Generate verification code
        $verification_code = rand(100000, 999999);

        // Update buyer's verification code
        $buyer->verification_code = $verification_code;
        $buyer->save();

        // Send verification email
        Mail::to($email)->send(new resendBuyerEmailAuth($buyer, $buyer->firstname));

        return response()->json([
            'message' => 'Verification code sent to the provided email address.',
        ], 200);
    }
}
