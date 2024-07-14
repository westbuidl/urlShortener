<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\CompanyBuyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        if ($buyer instanceof Buyer || $buyer instanceof CompanyBuyer) {
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
    }
    // Begin profile picture update function

    public function updateBuyerProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'required|image|mimes:jpg,png,bmp|max:1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $buyer = $request->user();

        if ($buyer instanceof Buyer || $buyer instanceof CompanyBuyer) {
            if ($request->hasFile('profile_photo')) {
                // Delete old profile photo if it exists
                if ($buyer->profile_photo) {
                    $old_path = public_path('/uploads/profile_images/' . $buyer->profile_photo);
                    if (File::exists($old_path)) {
                        File::delete($old_path);
                    }
                }

                // Save new profile photo
                $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
                $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);

                // Update profile photo in the database
                $buyer->update([
                    'profile_photo' => $image_name
                ]);

                return response()->json([
                    'message' => 'Profile Picture successfully updated',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No profile photo uploaded',
                ], 400);
            }
        } else {
            return response()->json([
                'message' => 'Buyer not found',
            ], 404);
        }
    }




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

            // Check if the buyer is an instance of Buyer or CompanyBuyer
            if ($buyer instanceof Buyer || $buyer instanceof CompanyBuyer) {
                // Find the buyer in the respective model
                if ($buyer instanceof Buyer) {
                    $buyer = Buyer::where('buyerId', $buyerId)->first();
                } else {
                    $buyer = CompanyBuyer::where('companyBuyerId', $buyerId)->first();
                }

                // Check if the buyer has a profile picture
                if ($buyer && !empty($buyer->profile_photo)) {
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
            } else {
                return response()->json([
                    'message' => 'Buyer not found.',
                ], 404);
            }
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'message' => 'Error deleting profile picture.',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }


    public function getBuyerProfile(Request $request, $buyerId)
    {


        // Get the authenticated user
        $authenticatedBuyer = $request->user();

        // Check if the authenticated user is the one being requested
        if ($authenticatedBuyer->buyerId != $buyerId && $authenticatedBuyer->companyBuyerId != $buyerId) {
            return response()->json([
                'message' => 'Unauthorized access.',
            ], 403);
        }

        $buyer = Buyer::where('buyerId', $buyerId)->first();

        if (!$buyer) {
            $buyer = CompanyBuyer::where('companyBuyerId', $buyerId)->first();
        }

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

    public function deleteBuyerAccount(Request $request, $buyerId)
    {
        try {
            // Retrieve the authenticated user's ID
            $authenticatedUser = Auth::user();


            // Ensure that the user is logged in and matches the requested buyer ID
            if (!$authenticatedUser || ($authenticatedUser->buyerId != $buyerId && $authenticatedUser->companyBuyerId != $buyerId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Buyer not authenticated or mismatched buyer ID.',
                ], 401);
            }

            // Check if the buyer exists in the Buyer model
            $buyer = Buyer::where('buyerId', $buyerId)->first();

            // If not found, check in the CompanyBuyer model
            if (!$buyer) {
                $buyer = CompanyBuyer::where('companyBuyerId', $buyerId)->first();
            }

            // Check if the buyer exists
            if (!$buyer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Buyer not found.',
                ], 404);
            }


            // Delete the profile picture from the filesystem if it exists
            if (!empty($buyer->profile_photo)) {
                $imagePath = public_path('/uploads/profile_images/' . $buyer->profile_photo);
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }

            // Delete any other associated data if needed (e.g., orders, cart items)

            // Delete the buyer's account
            $buyer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Buyer account deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'status' => false,
                'message' => 'Error deleting buyer account.',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }
}
