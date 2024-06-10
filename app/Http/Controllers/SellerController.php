<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
//use Illuminate\Validation\Validator;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\sellerSignupEmail;
use App\Mail\PasswordResetEmail;
use App\Mail\sellerEmailVerified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\sellerPasswordResetEmail;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    //
    //function for seller registration
    public function sellerSignup(Request $request)
    {
        $sellerId = 'AGS' . rand(10000000, 99999999);
        $verification_code = rand(100000, 999999);

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:2|max:100',
            'lastname' => 'required|min:2|max:100',
            'email' => 'required|email|unique:sellers',
            'phone' => 'required|min:2|max:100|unique:sellers',
            'product' => 'min:2|max:100',
            'product_category' => 'required|min:2|max:100',
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

        $seller = Seller::create([
            'sellerId' => $sellerId,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'product' => $request->product,
            'product_category' => $request->product_category,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'address' => $request->address,
            'zipcode' => $request->zipcode,
            'password' => Hash::make($request->password),
            'verification_code' => $verification_code



        ]);

        Mail::to($request->email)->send(new sellerSignupEmail($seller, $seller->firstname));


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



    //Verify seller email begins

    public function verifySellerEmail(Request $request)
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


        $seller = Seller::where('verification_code', $request->otp)->first();

        // If individual user is found
        if ($seller) {
            // Check if email is already verified
            if ($seller->email_verified_at) {

                return response()->json([
                    'message' => 'Email already verified.',
                ], 400);
            }

            // Mark email as verified
            $seller->email_verified_at = Carbon::now();
            $seller->is_verified = true;
            $seller->save();

            Mail::to($seller->email)->send(new sellerEmailVerified($seller, $seller->firstname));
            return response()->json([
                'message' => 'Seller account  verified. Proceed to login.',
            ], 200);
        } else {
            // If individual user is not found or OTP is incorrect
            return response()->json([
                'message' => 'Invalid OTP. Please try again.',
            ], 400);
        }
    }

    //function for seller login
    public function loginSeller(Request $request)
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

        $seller = Seller::where('email', $request->email)->first();

        if ($seller) {
            if ($seller->email_verified_at) {
                if (Hash::check($request->password, $seller->password)) {
                    $token = $seller->createToken('auth-token')->plainTextToken;
                    // Store user ID in session
                    session(['buyerId' => $seller->sellerId]);
                    session(['email' => $seller->email]);

                    //return Redirect::route('user.dashboard')->with('token', $token);

                    return response()->json([
                        'message' => 'Login Successful',
                        'token' => $token,
                        'data' => $seller
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

    /*public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Seller logged out',

        ], 200);
    }*/

    /*public function sellerLogout(Request $request)
    {
        // Assuming 'BuyerId' is a field in the authenticated user model

        $seller = auth()->user();
        

        //$buyerId = $request->input('buyerId');

        // Find the buyer using the BuyerId
        $seller = Seller::where('sellerId', $seller->sellerId)->first();

        if ($seller) {
            // Revoke all tokens for the buyer
            $seller->tokens()->delete();

            return response()->json([
                'message' => 'Seller logged out successfully',
            ], 200);
        } else {
            return response()->json([
                'message' => 'Seller not found',
            ], 404);
        }
    }*/
    public function sellerLogout(Request $request)
{
    $seller = $request->user();

    if ($seller) {
        $seller->tokens()->delete();

        return response()->json([
            'message' => 'Seller logged out successfully',
        ], 200);
    } else {
        return response()->json([
            'message' => 'Seller not found',
        ], 404);
    }
}



    //Seller forgot password
    public function sellerPasswordReset(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:sellers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email not found.',
            ], 400);
        }

        //Generate new code for password
        $reset_password = rand(10000000, 99999999);

        $seller = Seller::where('email', $request->email)->first();
        // $buyer->password = $reset_password;
        $seller->update([
            'password' => Hash::make($reset_password)
        ]);

        $seller->save();

        Mail::to($seller->email)->send(new sellerPasswordResetEmail($seller, $reset_password, $seller->firstname));
        return response()->json([
            'message' => 'Password reset code sent.',
            'password_data' => $reset_password
        ], 200);
    }

    public function resendSellerEmailAuth(Request $request, $email)
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
        $seller = Seller::where('email', $email)->first();

        // Check if buyer exists
        if (!$seller) {
            return response()->json([
                'message' => 'User not found for the provided email address.',
            ], 404);
        }

        if ($seller->is_verified) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ], 400);
        }
        // Generate verification code
        $verification_code = rand(100000, 999999);

        // Update buyer's verification code
        $seller->verification_code = $verification_code;
        $seller->save();

        // Send verification email
        Mail::to($email)->send(new sellerSignupEmail($seller, $seller->firstname));

        return response()->json([
            'message' => 'Verification code sent to the provided email address.',
        ], 200);
    }
}
