<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\IndividualAccount;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //register form
    public function individual(Request $request){
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
            'firstname'=>'required|min:2|max:100',
            'lastname'=>'required|min:2|max:100',
            'email'=>'required|email|unique:individual_accounts',
            'phone'=>'required|min:2|max:100|unique:individual_accounts',
            'product'=>'required|min:2|max:100',
            'country'=>'required|min:2|max:100',
            'state'=>'required|min:2|max:100',
            'city'=>'required|min:2|max:100',
            'zipcode'=>'required|min:2|max:100',
            'password'=>'required|min:6|max:100',
            'confirm_password'=>'required|same:password'

        ]);
       if ($validator->fails()) {
        return response()->json([
            'message'=>'Validations fails',
            'error'=>$validator->errors()
        ],422);
    }

   $user=IndividualAccount::create([
            'firstname'=>$request->firstname,
            'lastname'=>$request->firstname,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'product'=>$request->product,
            'country'=>$request->country,
            'state'=>$request->state,
            'city'=>$request->city,
            'zipcode'=>$request->zipcode,
            'password'=>Hash::make($request->password)
            //'confirm_password'=>'required|same:password'
   ]);

    return response()->json([
        'message'=>'Registration successful',
        'data'=>$user
    ],200);
    
    }

}


