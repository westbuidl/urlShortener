<?php

namespace App\Http\Controllers;

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
             'businessregnumber' => 'nullable|max:100',
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
             'businessregnumber'=>$request->businessregnumber,
             'businessphone' =>$request->businessphone,
             'products' =>$request->products,
             'businessaddress' =>$request->businessaddress,
             'country' =>$request->country,
             'city' =>$request->city,
             'state' =>$request->state,
             'zipcode' =>$request->zipcode
 
         ]);
        
          return response()->json([
             'message'=> 'Account settings updated',
          ],200);
 
     }//End update account settings function
}



