<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Buyer;
use App\Mail\buyerSignupEmail;
use Illuminate\Support\Str;
use App\Mail\buyerEmailVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\PasswordResetEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\buyerPasswordResetEmail;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\MailController;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;

class BuyerController extends Controller
{
    //function for user registration
    public function signup(Request $request)
    {
        $buyerId = 'AGB' . rand(00000000, 99999999);
        $verification_code = rand(000000, 999999);
       
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
            'zipcode' => $request->zipcode,
            'password' => Hash::make($request->password),
            'verification_code' => $verification_code



        ]);

        Mail::to($request->email)->send(new buyerSignupEmail($buyer,$buyer->firstname));


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

    //function to logout
    /* public function logout(Request $request)
{
    $request->user()->tokens()->delete();

    // Remove user ID from session if you're also using session-based authentication
    // $request->session()->forget('user_id');

    return response()->json([
        'message' => 'User logged out successfully.',
    ], 200);
} */


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'User logged out',

        ], 200);
    }

    //function to check and verify email


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

        Mail::to($buyer->email)->send(new buyerPasswordResetEmail($buyer, $reset_password,$buyer->firstname));
        return response()->json([
            'message' => 'Password reset code sent.',
            'password_data' => $reset_password
        ], 200);
    }


    //function to get user profile
    public function getBuyerProfile(Request $request, $buyerId)
    {
        // Retrieve the authenticated user
        // $user = $request->user();
        $buyer = Buyer::find($buyerId);

        // Check if the user exists
        //if ($user && $user->id == $userID) 
        if ($buyer) {
            // Return user information along with profile picture
            $profile_picture = asset('uploads/profile_images/' . $buyer->profile_photo);
            //$profile_picture => asset('uploads/profile_images/' . $user->profile_photo);
            return response()->json([
                'message' => 'Buyer profile found.',
                'data' => [
                    'buyer' => $buyer,
                    'profile_picture' => $profile_picture
                ]
            ], 200);
        } else {
            // If the user is not found, return an error message
            return response()->json([
                'message' => 'Buyer not found.',
            ], 404);
        }
    }




    //End function to get user profile




    //function to resend verification code
    /* public function resendverificationcode(Request $request)
    {
        $email = Session::get('email');

        if (!$email) {
            return response()->json([
                'message' => 'Email not found.',
            ], 400);

            $buyer = Buyer::where('email', $email)->first();

            if (!$buyer) {
                return response()->json([
                    'message' => 'user not found.',
                ], 400);
            }
            $verification_code = rand(000000, 999999);

            $buyer->verification_code = $verification_code;
            //$buyer->is_verified = true;
            $buyer->save();

            Mail::to($request->email)->send(new buyerSignupEmail($buyer));


            return response()->json([
                'message' => 'Registration successful Verification Email Sent'
            ], 200);
        }
    }*/
}
