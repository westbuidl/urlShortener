<?php

namespace App\Http\Controllers;

use App\Mail\Bpasswordreset;
use Illuminate\Http\Request;
use App\Models\CompanySeller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\companySellerEmailVerified;
use Illuminate\Support\Facades\Validator;
use App\Mail\resendCompanySellerEmailAuth;
use App\Mail\companySellerPasswordResetEmail;

class CompanySellerController extends Controller
{
    //


    public function companySellerSignup(Request $request)
    {

        $companySellerId = 'AGCS' . rand(1000, 9999);
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
            'companyregnumber' => 'required|unique:company_sellers',
            'companyemail' => 'required|unique:company_sellers',
            'companyphone' => 'required|min:2|max:100|unique:company_sellers',
            'products' => 'required|min:2|max:100',
            'product_category' => 'required|min:2|max:100',
            'companyaddress' => 'min:2|max:100',
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

        $companySeller = CompanySeller::create([
            'companySellerId' => $companySellerId,
            'companyname' => $request->companyname,
            'companyregnumber' => $request->companyregnumber,
            'companyemail' => $request->companyemail,
            'companyphone' => $request->companyphone,
            'products' => $request->products,
            'product_category' => $request->product_category,
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
        Mail::to($request->companyemail)->send(new ($companySeller));
        return response()->json([
            'message' => 'Business registration successful Check email for verification code',
            'data' => $companySeller
        ], 200);
    }


    public function companySellerLogin(Request $request)
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

        $companySeller = CompanySeller::where('email', $request->email)->first();

        if ($companySeller) {
            if ($companySeller->email_verified_at) {
                if (Hash::check($request->password, $companySeller->password)) {
                    $token = $companySeller->createToken('auth-token')->plainTextToken;

                    // Store user ID in session
                    session(['companySellerId' => $companySeller->companySellerId]);
                    session(['email' => $companySeller->companyemail]);

                    return response()->json([
                        'message' => 'Login Successful',
                        'token' => $token,
                        'data' => $companySeller
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

    public function companySellerVerifyMail(Request $request)
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
        $companySeller = CompanySeller::where('verification_code', $request->otp)->first();

        // If individual user is found
        if ($companySeller) {
            // Check if email is already verified
            if ($companySeller->email_verified_at) {
                return response()->json([
                    'message' => 'Email already verified.',
                ], 400);
            }

            // Mark email as verified
            $companySeller->email_verified_at = Carbon::now();
            $companySeller->is_verified = true;
            $companySeller->save();

            Mail::to($companySeller->companyemail)->send(new companySellerEmailVerified($companySeller));
            return response()->json([
                'message' => 'Email verified. Proceed to login.',
            ], 200);
        } else {
            // If individual user is not found or OTP is incorrect
            return response()->json([
                'message' => 'OTP Invalid, Please try again.',
            ], 400);
        }
    }

    //function to reset password
    public function companySellerResetPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:company_sellers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email not found.',
            ], 400);
        }

        //Generate new code for password
        $reset_password = rand(10000000, 99999999);

        $companySeller = CompanySeller::where('companyemail', $request->email)->first();
        // $individualuser->password = $reset_password;
        $companySeller->update([
            'password' => Hash::make($reset_password)
        ]);

        $companySeller->save();

       // Mail::to($companySeller->businessemail)->send(new Bpasswordreset($companySeller, $reset_password));
        Mail::to($companySeller->companyemail)->send(new companySellerPasswordResetEmail($companySeller, $reset_password, $companySeller->companyname));
       
        return response()->json([
            'message' => 'Password reset code sent.',
            'password_data' => $reset_password
        ], 200);
    }

    public function resendCompanySellerEmailAuth(Request $request, $email)
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
        $companyseller = CompanySeller::where('email', $email)->first();

        // Check if buyer exists
        if (!$companyseller) {
            return response()->json([
                'message' => 'User not found for the provided email address.',
            ], 404);
        }


        if ($companyseller->is_verified) {
            return response()->json([
                'message' => 'Email address is already verified.',
            ], 400);
        }
        // Generate verification code
        $verification_code = rand(100000, 999999);

        // Update buyer's verification code
        $companyseller->verification_code = $verification_code;
        $companyseller->save();

        // Send verification email
        Mail::to($email)->send(new resendCompanySellerEmailAuth($companyseller, $verification_code));

        return response()->json([
            'message' => 'Verification code sent to the provided email address.',
        ], 200);
    }

     
    
    // Begin profile picture update function
     public function updateCompanySellerProfilePicture(Request $request)
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
         $companySeller = $request->user();
         if ($request->hasFile('profile_photo')) {
             if ($companySeller->profile_photo) {
                 $old_path = public_path() . '/uploads/profile_images/' . $companySeller->profile_photo;
                 if (File::exists($old_path)) {
                     File::delete($old_path);
                 }
             }
             $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
             $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);
         } else {
             $image_name = $companySeller->profile_photo;
         }
 
         $companySeller->update([
             'profile_photo' => $image_name
 
         ]);
         return response()->json([
             'message' => 'Profile Picture successfully updated',
 
         ], 200);
     } // End profile update function
 
 
     //Begin update account settings function
     public function updateCompanySellerAccountDetails(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'firstname' => 'nullable|max:100',
             'lastname' => 'nullable|max:100',
             //'email' => 'nullable|max:100',
             'phone' => 'nullable|max:100',
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
         $companySeller = $request->user();
         $companySeller->update([
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
             'message' => 'Company Seller Contact information Changed',
         ], 200);
     } //End update account settings function
 
 
 
 
    
 
 
     //Delete Company Seller profile picture
 
     public function deleteCompanySellerProfilePicture(Request $request, $companySellerId)
     {
         try {
 
             $companySeller = $request->user();
 
             // If validation fails, return error response
 
 
             // Find the buyer in the database
             //$companySeller = Seller::findOrFail($request->companySellerId);
             $companySeller = CompanySeller::where('companySellerId', $companySellerId)->first();
 
             // Check if the buyer has a profile picture
             if (!empty($companySeller->profile_photo)) {
                 // Delete the profile picture from the filesystem
                 $imagePath = public_path('/uploads/profile_images/' . $companySeller->profile_photo);
                 if (File::exists($imagePath)) {
                     File::delete($imagePath);
                 }
 
                 // Update the buyer's profile picture field to null
                 $companySeller->profile_photo = null;
                 $companySeller->save();
 
 
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
 
     public function getCompanySellerProfile(Request $request, $companySellerId)
     {
         try {
             // Retrieve the companySeller by ID
             //$companySeller = Seller::findOrFail($companySellerId);
             $companySeller = CompanySeller::where('companySellerId', $companySellerId)->first();
     
             // Return companySeller information along with profile picture
             $profile_picture = asset('uploads/profile_images/' . $companySeller->profile_photo);
     
             return response()->json([
                 'message' => 'Company Seller profile found.',
                 'data' => [
                     'companySeller' => $companySeller,
                     'profile_picture' => $profile_picture
                 ]
             ], 200);
         } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             // Handle the case when the companySeller is not found
             return response()->json([
                 'message' => 'Error: Seller not found with ID ' . $companySellerId,
             ], 404);
         } catch (\Exception $e) {
             // Handle any other exceptions that may occur
             return response()->json([
                 'message' => 'Error: Something went wrong.',
                 'error' => $e->getMessage()
             ], 500);
         }
     }


}