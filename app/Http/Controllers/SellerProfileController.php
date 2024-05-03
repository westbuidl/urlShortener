<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use App\Mail\addBankAccountEmail;
use App\Mail\bankAccountSavedEmail;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SellerProfileController extends Controller
{
    //Begin function to change password

    public function changeSellerPassword(Request $request)
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
        $seller = $request->user();
        if (Hash::check($request->old_password, $seller->password)) {
            $seller->update([
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

    // Begin profile picture update function
    public function updateSellerProfilePicture(Request $request)
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
        $seller = $request->user();
        if ($request->hasFile('profile_photo')) {
            if ($seller->profile_photo) {
                $old_path = public_path() . '/uploads/profile_images/' . $seller->profile_photo;
                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }
            $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
            $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);
        } else {
            $image_name = $seller->profile_photo;
        }

        $seller->update([
            'profile_photo' => $image_name

        ]);
        return response()->json([
            'message' => 'Profile Picture successfully updated',

        ], 200);
    } // End profile update function


    //Begin update account settings function
    public function updateSellerAccountDetails(Request $request)
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
        $seller = $request->user();
        $seller->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            //'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zipcode' => $request->zipcode


        ]);

        return response()->json([
            'message' => 'Seller Contact information Changed',
        ], 200);
    } //End update account settings function







    //Delete buyer profile picture

    public function deleteSellerProfilePicture(Request $request, $sellerId)
    {
        try {

            $seller = $request->user();

            // If validation fails, return error response


            // Find the buyer in the database
            //$seller = Seller::findOrFail($request->sellerId);
            $seller = Seller::where('sellerId', $sellerId)->first();

            // Check if the buyer has a profile picture
            if (!empty($seller->profile_photo)) {
                // Delete the profile picture from the filesystem
                $imagePath = public_path('/uploads/profile_images/' . $seller->profile_photo);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }

                // Update the buyer's profile picture field to null
                $seller->profile_photo = null;
                $seller->save();


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

    public function getSellerProfile(Request $request, $sellerId)
    {
        try {
            // Retrieve the seller by ID
            //$seller = Seller::findOrFail($sellerId);
            $seller = Seller::where('sellerId', $sellerId)->first();

            // Return seller information along with profile picture
            $profile_picture = asset('uploads/profile_images/' . $seller->profile_photo);

            return response()->json([
                'message' => 'Seller profile found.',
                'data' => [
                    'seller' => $seller,
                    'profile_picture' => $profile_picture
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case when the seller is not found
            return response()->json([
                'message' => 'Error: Seller not found with ID ' . $sellerId,
            ], 404);
        } catch (\Exception $e) {
            // Handle any other exceptions that may occur
            return response()->json([
                'message' => 'Error: Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /* public function addBankAccount(Request $request)
    {
        // Get the authenticated seller's ID
        $sellerId = auth()->user()->sellerId;

       

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|min:2|max:100',
            'account_number' => 'required|min:2|max:100',
            'bank_name' => 'required|min:2|max:100'


        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations fails',
                'error' => $validator->errors()
            ], 422);
        }

        
        // Save OTP in the seller's record for verification
        $seller = Seller::where('sellerId', $sellerId)->first();
        // $seller = Seller::find($sellerId);
        //$seller->verification_code = $otp;
        $seller->save();

        // Send OTP to seller's email
        Mail::to($seller->email)->send(new addBankAccountEmail($seller, $seller->firstname));

        return response()->json([
            'message' => 'Account saved.',
        ], 200);
    }

    public function verifyBankAccount(Request $request)
    {
        // Get the authenticated seller's ID
        $sellerId = auth()->user()->sellerId;

        // Find the seller by ID
        $seller = Seller::where('sellerId', $sellerId)->first();

        // Check if seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'account_name' => 'required|min:2|max:100',
            'account_number' => 'required|min:2|max:100',
            'bank_name' => 'required|min:2|max:100'


        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations fails',
                'error' => $validator->errors()
            ], 422);
        }


        // Verify OTP
        if ($request->otp != $seller->verification_code) {
            return response()->json([
                'message' => 'Invalid OTP. Please try again.',
            ], 400);
        }

        // Update seller's bank account information
        $seller->account_name = $request->account_name;
        $seller->account_number = $request->account_number;
        $seller->bank_name = $request->bank_name;
        $seller->save();

        // Clear OTP after successful verification
        $seller->verification_code = null;
        $seller->save();


        Mail::to($seller->email)->send(new bankAccountSavedEmail($seller, $seller->firstname));
        return response()->json([
            'message' => 'Bank account information successfully added.',
            'data' => $seller
        ], 200);
    }*/

    public function addBankAccount(Request $request)
    {
        // Get the authenticated seller's ID
        $sellerId = auth()->user()->sellerId;

        // Retrieve the authenticated seller
        $seller = Seller::where('sellerId', $sellerId)->first();

        // Ensure the seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|min:2|max:100',
            'account_number' => 'required|min:2|max:100',
            'bank_name' => 'required|min:2|max:100'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'error' => $validator->errors()
            ], 422);
        }

        // Extract first name and last name from seller's account
        $sellerFirstName = $seller->firstname;
        $sellerLastName = $seller->lastname;

        // Concatenate first name and last name
        $sellerFullName = $sellerFirstName . ' ' . $sellerLastName;

        // Check if the entered account name matches the seller's full name
        if ($request->account_name !== $sellerFullName) {
            return response()->json([
                'message' => 'Bank account name does not match your registered name.'
            ], 400);
        }

        // Update seller's bank account information
        $seller->account_name = $request->account_name;
        $seller->account_number = $request->account_number;
        $seller->bank_name = $request->bank_name;
        $seller->save();

        // Clear OTP after successful verification
        $seller->verification_code = null;
        $seller->save();


        Mail::to($seller->email)->send(new bankAccountSavedEmail($seller, $seller->firstname));
        return response()->json([
            'message' => 'Bank account information successfully added.',
            'data' => $seller
        ], 200);
    }

    public function getBankAccountDetails(Request $request)
    {
        // Get the authenticated seller's ID
        $sellerId = auth()->user()->sellerId;

        // Find the seller by ID
        //$seller = Seller::find($sellerId);
        $seller = Seller::where('sellerId', $sellerId)->first();

        // Check if seller exists
        if (!$seller) {
            return response()->json([
                'message' => 'Seller not found.',
            ], 404);
        }

        // Check if the seller has bank account details
        if (!$seller->bank_name || !$seller->account_number) {
            return response()->json([
                'message' => 'Seller bank account details not found.',
            ], 404);
        }

        // Construct response with seller's bank account details
        $bankAccountDetails = [
            'account_name' => $seller->account_name,
            'bank_name' => $seller->bank_name,
            'account_number' => $seller->account_number,
        ];

        return response()->json([
            'message' => 'Seller bank account details retrieved successfully.',
            'data' => $bankAccountDetails
        ], 200);
    }
}
