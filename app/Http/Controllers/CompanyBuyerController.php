<?php

namespace App\Http\Controllers;

use App\Mail\Bpasswordreset;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
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

        $companyBuyerId = 'AGCB' . rand(1000, 9999);
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

        Mail::to($companyBuyer->companyemail)->send(new companyBuyerPasswordResetEmail($companyBuyer, $reset_password, $companyBuyer->companyname));
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
        $companybuyer = CompanyBuyer::where('companyemail', $email)->first();
    
        // Check if buyer exists
        if (!$companybuyer) {
            return response()->json([
                'message' => 'User not found for the provided email address.',
            ], 404);
        }
    
        if ($companybuyer->is_verified) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ], 400);
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




     // Begin profile picture update function
     public function updateCompanyBuyerProfilePicture(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'profile_photo' => 'required|image|mimes:jpg,png,bmp'
 
         ]);
         if ($validator->fails()) {
             return response()->json([
                 'message' => 'validations fails',
                 'errors' => $validator->errors()
             ], 422);
         }
         $companyBuyer = $request->user();
         if ($request->hasFile('profile_photo')) {
             if ($companyBuyer->profile_photo) {
                 $old_path = public_path() . '/uploads/profile_images/' . $companyBuyer->profile_photo;
                 if (File::exists($old_path)) {
                     File::delete($old_path);
                 }
             }
             $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
             $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);
         } else {
             $image_name = $companyBuyer->profile_photo;
         }
 
         $companyBuyer->update([
             'profile_photo' => $image_name
 
         ]);
         return response()->json([
             'message' => 'Profile Picture successfully updated',
 
         ], 200);
     } // End profile update function
 
 
     //Begin update account settings function
     public function updateCompanyBuyerAccountDetails(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'companyname' => 'nullable|max:100',
             'companyregnumber' => 'nullable|max:100',
             //'email' => 'nullable|max:100',
             'companyphone' => 'nullable|max:100',
             'companyemail' => 'nullable|max:100',
             'companyaddress' => 'nullable|max:100',
             'country' => 'nullable|max:100',
             'state' => 'nullable|max:100',
             'city' => 'nullable|max:100',
             'zipcode' => 'nullable|max:100'
             
         ]);
         if ($validator->fails()) {
             return response()->json([
                 'message' => 'Validations failed',
                 'error' => $validator->errors()
             ], 422);
         }
         $companyBuyer = $request->user();
         $companyBuyer->update([
             'companyname' => $request->companyname,
             'companyregnumber' => $request->companyregnumber,
             //'email' => $request->email,
             'companyphone' => $request->companyphone,
             'companyemail' => $request->companyemail,
             'companyaddress' => $request->companyaddress,
             'country' => $request->country,
             'city' => $request->city,
             'state' => $request->state,
             'zipcode' => $request->zipcode
             
 
         ]);
 
         return response()->json([
             'message' => 'Company Buyer Contact information Changed',
         ], 200);
     } //End update account settings function
 
 
 
 
    
 
 
     //Delete Company Seller profile picture
 
     public function deleteCompanyBuyerProfilePicture(Request $request, $companyBuyerId)
     {
         try {
 
             $companyBuyer = $request->user();
 
             // If validation fails, return error response
 
 
             // Find the buyer in the database
             //$companySeller = Seller::findOrFail($request->companyBuyerId);
             $companyBuyer = CompanyBuyer::where('companyBuyerId', $companyBuyerId)->first();
 
             // Check if the buyer has a profile picture
             if (!empty($companyBuyer->profile_photo)) {
                 // Delete the profile picture from the filesystem
                 $imagePath = public_path('/uploads/profile_images/' . $companyBuyer->profile_photo);
                 if (File::exists($imagePath)) {
                     File::delete($imagePath);
                 }
 
                 // Update the buyer's profile picture field to null
                 $companyBuyer->profile_photo = null;
                 $companyBuyer->save();
 
 
                 return response()->json([
                     'message' => 'Profile picture deleted successfully.',
                 ], 200);
             } else {
                 return response()->json([
                     'message' => 'no profile picture found for this Seller',
                 ], 400);
             }
         } catch (\Exception $e) {
             // Handle any exceptions that occur during the deletion process
             return response()->json([
                 'message' => 'Error deleting profile picture.',
                 'error' => $e->getMessage(), // Include the error message for debugging
             ], 500);
         }
     }
 
     public function getCompanyBuyerProfile(Request $request, $companyBuyerId)
     {
         try {
             // Retrieve the companySeller by ID
             //$companySeller = Seller::findOrFail($companyBuyerId);
             $companyBuyer = CompanyBuyer::where('companyBuyerId', $companyBuyerId)->first();
     
             // Return companySeller information along with profile picture
             $profile_picture = asset('uploads/profile_images/' . $companyBuyer->profile_photo);
     
             return response()->json([
                 'message' => 'Company Buyer profile found.',
                 'data' => [
                     'companyBuyer' => $companyBuyer,
                     'profile_picture' => $profile_picture
                 ]
             ], 200);
         } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             // Handle the case when the companySeller is not found
             return response()->json([
                 'message' => 'Error: Company Buyer not found with ID ' . $companyBuyerId,
             ], 404);
         } catch (\Exception $e) {
             // Handle any other exceptions that may occur
             return response()->json([
                 'message' => 'Error: Something went wrong.',
                 'error' => $e->getMessage()
             ], 500);
         }
     }

     public function companyBuyerChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:6|max:100',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }
        $companyBuyer = $request->user();
        if (Hash::check($request->old_password, $companyBuyer->password)) {
            $companyBuyer->update([
                'password' => Hash::make($request->password)
            ]);
            return response()->json([
                'message' => 'Password changed'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Old password does not match'
            ], 400);
        }
    } // End function to change password

}
