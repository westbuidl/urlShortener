<?php

namespace App\Http\Controllers;

use App\Models\BusinessAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SellerProfileContoller extends Controller
{
    //


    //Begin function to change password for sellers

    public function seller_change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|min:6|max:100',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations failed',
                'error' => $validator->errors()
            ], 422);
        }
        $user = $request->user();
        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
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
    } // End function to change password for sellers


     //Begin update account settings function for seller
     public function seller_account_setting(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'businessname' => 'nullable|max:100',
             //'businessregnumber' => 'nullable|max:100',
             'businessphone' => 'nullable|max:100',
             'products' => 'nullable|max:100',
             'businessaddress' => 'nullable|max:100',
             'country' => 'nullable|max:100',
             'city' => 'nullable|max:100',
             'state' => 'nullable|max:100',
             'zipcode' => 'nullable|max:100'
         ]);
         if ($validator->fails()) {
             return response()->json([
                 'message' => 'Validations failed',
                 'error' => $validator->errors()
             ], 422);
         }
         $user = $request->user();
         $user->update([
             //'firstname'=>$request->firstname,
             'businessname' =>$request->businessname,
             //'businessregnumber'=>$request->businessregnumber,
             'businessphone' =>$request->businessphone,
             'products' =>$request->products,
             'businessaddress' =>$request->businessaddress,
             'country' =>$request->country,
             'city' =>$request->city,
             'state' =>$request->state,
             'zipcode' =>$request->zipcode
 
         ]);
        
          return response()->json([
             'message'=> 'Seller Profile updated',
          ],200);
 
     }//End update account settings function



       // Begin profile picture update function for seller
    public function seller_update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_photo' => 'nullable|image|mimes:jpg,png,bmp'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'validations fails',
                'errors' => $validator->errors()
            ], 422);
        }
        $user = $request->user();
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                $old_path = public_path() . '/uploads/profile_images/' . $user->profile_photo;
                if (File::exists($old_path)) {
                    File::delete($old_path);
                }
            }
            $image_name = 'profile-image-' . time() . '.' . $request->profile_photo->extension();
            $request->profile_photo->move(public_path('/uploads/profile_images'), $image_name);
        } else {
            $image_name = $user->profile_photo;
        }

        $user->update([
            'profile_photo' => $image_name

        ]);
        return response()->json([
            'message' => 'Sellers Profile Picture successfully uploaded',

        ], 200);
    } // End profile update function



         //Delete user profile picture
   
         public function delete_businessprofilepicture(Request $request)
         {
             try {
     
                 $business = $request->user();
     
                 // If validation fails, return error response
     
     
                 // Find the user in the database
                 $business = BusinessAccount::findOrFail($request->id);
     
                 // Check if the user has a profile picture
                 if (!empty($business->profile_photo)) {
                     // Delete the profile picture from the filesystem
                     $imagePath = public_path('/uploads/profile_images/' . $business->profile_photo);
                     if (File::exists($imagePath)) {
                         File::delete($imagePath);
                     }
     
                     // Update the user's profile picture field to null
                     $business->profile_photo = null;
                     $business->save();
     
     
                     return response()->json([
                         'message' => 'Profile picture deleted successfully.',
                     ], 200);
                 } else {
                     return response()->json([
                         'message' => 'no profile picture found for this user',
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
     
}





