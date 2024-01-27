<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BusinessAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    //


    public function business(Request $request){
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
        $validator = Validator::make($request->all(),[
             'businessID'=>'required|min:2|max:100',
             'businessname'=>'required|min:2|max:100',
             'businessregnumber'=>'required|businessregnumber|unique:individual_accounts',
             'businessemail'=>'required|businessemail|unique:business_accounts',
             'businessphonenumber'=>'required|min:2|max:100|unique:business_accounts',
             'product'=>'required|min:2|max:100',
             'businessaddress'=>'required|min:2|max:100',
             'country'=>'required|min:2|max:100',
             'city'=>'required|min:2|max:100',
             'zipcode'=>'required|min:2|max:100',
             'password'=>'required|min:6|max:100',
             'confirm_password'=>'required|same:password'
 
         ]);
        if ($validator->fails()) {
         return response()->json([
             'message'=>'Business Registration failed',
             'error'=>$validator->errors()
         ],422);
     }
 
    $business=BusinessAccount::create([
                'businessID'=>$request->businessID,
                'businessname'=>$request->businessname,
                'businessregnumber'=>$request->businessregnumber,
                'businessemail'=>$request->businessemail,
                'businessphonenumber'=>$request->businessphonenumber,
                'product'=>$request->product,
                'businessaddress'=>$request->businessaddress,
                'country'=>$request->country,
                'city'=>$request->city,
                'zipcode'=>$request->zipcode,
                //'password'=>'required|min:6|max:100',
                'password'=>Hash::make($request->password)
             //'confirm_password'=>'required|same:password'
    ]);
 
     return response()->json([
         'message'=>'Registration successful',
         'data'=>$business
     ],200);
     
     }
}
