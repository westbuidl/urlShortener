<?php

namespace App\Http\Controllers;

use App\Mail\Bpasswordreset;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\companyBuyerSignupEmail;
use App\Mail\companyBuyerEmailVerified;
use App\Mail\resendCompanyBuyerEmailAuth;
use Illuminate\Support\Facades\Validator;
use App\Mail\companyBuyerPasswordResetEmail;

class CompanyBuyerController extends Controller
{
    //


    public function companyBuyerSignup(Request $request)
    {

        $companyBuyerId = 'AGCB' . rand(0000, 9999);
        $verification_code = rand(100000, 999999);


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
        $validator = Validator::make($request->all(), [
            //'businessID'=>'required|min:2|max:100',
            'companyname' => 'required|min:2|max:100',
            'companyregnumber' => 'required|unique:company_buyers',
            'companyemail' => 'required|unique:company_buyers',
            'companyphone' => 'required|min:2|max:100|unique:company_buyers',
            //'products' => 'required|min:2|max:100',
            'companyaddress' => 'required|min:2|max:100',
            'country' => 'required|min:2|max:100',
            'city' => 'required|min:2|max:100',
            'state' => 'required|min:2|max:100',
            'zipcode' => 'required|min:2|max:100',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Business Registration failed',
                'error' => $validator->errors()
            ], 422);
        }

        $companyBuyer = CompanyBuyer::create([
            'companyBuyerId' => $companyBuyerId,
            'companyname' => $request->companyname,
            'companyregnumber' => $request->companyregnumber,
            'companyemail' => $request->companyemail,
            'companyphone' => $request->companyphone,
            'products' => $request->products,
            'companyaddress' => $request->companyaddress,
            'country' => $request->country,
            'city' => $request->city,
            'state' => $request->state,
            'zipcode' => $request->zipcode,
            //'password'=>'required|min:6|max:100',
            'password' => Hash::make($request->password),
            'verification_code' => $verification_code
            //'confirm_password'=>'required|same:password'
        ]);
        Mail::to($request->companyemail)->send(new companyBuyerSignupEmail($companyBuyer, $companyBuyer->companyname, $companyBuyer->companyregnumber));
        return response()->json([
            'message' => 'Company registration successful Check email for verification code',
            'data' => $companyBuyer
        ], 200);
    }


    public function companyBuyerLogin(Request $request)
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
                        'data' => $companyBuyer
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
        } else {
            return response()->json([
                'message' => 'Incorrect Credentials',
            ], 400);
        }
    }


    //Email verification function

    public function companyBuyerVerifyMail(Request $request)
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
        $email = $request->input('email');
        $otp = $request->input('otp');

        // Find individual user with the provided email and OTP
        $companyBuyer = CompanyBuyer::where('verification_code', $request->otp)->first();

        // If individual user is found
        if ($companyBuyer) {
            // Check if email is already verified
            if ($companyBuyer->email_verified_at) {
                return response()->json([
                    'message' => 'Email already verified.',
                ], 400);
            }

            // Mark email as verified
            $companyBuyer->email_verified_at = Carbon::now();
            $companyBuyer->is_verified = true;
            $companyBuyer->save();

            Mail::to($companyBuyer->companyemail)->send(new companyBuyerEmailVerified($companyBuyer, $companyBuyer->companyname, $companyBuyer->companyregnumber));
            return response()->json([
                'message' => 'Company email verified. Proceed to login.',
            ], 200);
        } else {
            // If individual user is not found or OTP is incorrect
            return response()->json([
                'message' => 'OTP Invalid, Please try again.',
            ], 400);
        }
    }

    //function to reset password
    public function companyBuyerResetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:company_buyers,companyemail',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email not found.',
            ], 400);
        }

        //Generate new code for password
        $reset_password = rand(10000000, 99999999);

        $companyBuyer = CompanyBuyer::where('companyemail', $request->email)->first();
       // $individualuser->password = $reset_password;
         $companyBuyer->update([
                'password' => Hash::make($reset_password)
            ]);
        
        $companyBuyer->save();

        Mail::to($companyBuyer->companyemail)->send(new companyBuyerPasswordResetEmail($companyBuyer,$reset_password));
        return response()->json([
            'message' => 'Password reset code sent.',
            'password_data' => $reset_password
        ], 200);
    }

    public function resendCompanyBuyerEmailAuth(Request $request, $email)
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
        $companybuyer = CompanyBuyer::where('email', $email)->first();
    
        // Check if buyer exists
        if (!$companybuyer) {
            return response()->json([
                'message' => 'User not found for the provided email address.',
            ], 404);
        }
    
        // Generate verification code
        $verification_code = rand(100000, 999999);
    
        // Update buyer's verification code
        $companybuyer->verification_code = $verification_code;
        $companybuyer->save();
    
        // Send verification email
        Mail::to($email)->send(new resendCompanyBuyerEmailAuth($companybuyer, $verification_code));
    
        return response()->json([
            'message' => 'Verification code sent to the provided email address.',
        ], 200);
    }
}
