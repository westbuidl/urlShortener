<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Buyer;

class BuyerProfileController extends Controller
{
    //Begin function to change password

    public function changeBuyerPassword(Request $request)
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
        $buyer = $request->user();
        if (Hash::check($request->old_password, $buyer->password)) {
            $buyer->update([
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
    public function updateBuyerProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'required|image|mimes:jpg,png,bmp|max:1024',

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'validations fails',
                'errors' => $validator->errors()
            ], 422);
        }
        $buyer = $request->user();
        if ($request->hasFile('profile_photo')) {
            if ($buyer->profile_photo) {
                $old_path = public_path() . '/uploads/profile_images/' . $buyer->profile_photo;
                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }
            $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
            $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);
        } else {
            $image_name = $buyer->profile_photo;
        }

        $buyer->update([
            'profile_photo' => $image_name

        ]);
        return response()->json([
            'message' => 'Profile Picture successfully updated',

        ], 200);
    } // End profile update function


    //Begin update account settings function
    public function updateBuyerAccountDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'nullable|max:100',
            'lastname' => 'nullable|max:100',
            'email' => 'nullable|max:100',
            'phone' => 'nullable|max:100'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }
        $buyer = $request->user();
        $buyer->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'phone' => $request->phone

        ]);

        return response()->json([
            'message' => 'Buyer Contact information Changed',
        ], 200);
    } //End update account settings function




    //Begin update billing address function
    public function updateBuyerBillingAddress(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'country' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'city' => 'nullable|max:100',
            'zipcode' => 'nullable|max:100',
            'address' => 'nullable|max:100',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }
        $buyer = $request->user();
        $buyer->update([
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'zipcode' => $request->zipcode,
            'address' => $request->address

        ]);

        return response()->json([
            'message' => 'Buyer Billing Address updated',
        ], 200);
    } //End update billing address function


    //Delete buyer profile picture

    public function deleteBuyerProfilePicture(Request $request, $buyerId)
    {
        try {

            $buyer = $request->user();

            // If validation fails, return error response


            // Find the buyer in the database
            //$buyer = Buyer::findOrFail($request->buyerId);
            $buyer = Buyer::where('buyerId', $buyerId)->first();

            // Check if the buyer has a profile picture
            if (!empty($buyer->profile_photo)) {
                // Delete the profile picture from the filesystem
                $imagePath = public_path('/uploads/profile_images/' . $buyer->profile_photo);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }

                // Update the buyer's profile picture field to null
                $buyer->profile_photo = null;
                $buyer->save();


                return response()->json([
                    'message' => 'Profile picture deleted successfully.',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'no profile picture found for this buyer',
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






    /* public function delete_buyerprofilepicture(Request $request)
    {
        try {
            // Validate request inputs
            $validator = Validator::make($request->all(), [
                'buyer_id' => 'required|exists:individual_accounts,id',
            ]);

            // If validation fails, return error response
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'buyer not found.',
                    'error' => $validator->errors()->first(),
                ], 400);
            }

            // Get the authenticated buyer
            $authenticatedbuyer = $request->user();

            // Find the buyer in the database by buyer ID
            $buyer = Buyer::findOrFail($request->buyer_id);

            // Check if the authenticated buyer is the owner of the profile picture
            if ($authenticatedbuyer->id !== $buyer->id) {
                return response()->json([
                    'message' => 'You are not authorized to delete this profile picture.',
                ], 403);
            }

            // Check if the buyer has a profile picture
            if (!empty($buyer->profile_photo)) {
                // Delete the profile picture from the filesystem
                $imagePath = public_path('/uploads/profile_images/' . $buyer->profile_photo);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }

                // Update the buyer's profile picture field to null
                $buyer->profile_photo = null;
                $buyer->save();
            }

            return response()->json([
                'message' => 'Profile picture deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'message' => 'Error deleting profile picture.',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }*/
}
