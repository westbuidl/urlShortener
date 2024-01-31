<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\IndividualAccount;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //function for user registration
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
            'password'=>Hash::make($request->password),
             //$user->verification_code = sha1(time());
             //$user->verification_code= sha1(time());
            //'confirm_password'=>'required|same:password'

            
   ]);
   //send email after registration
   if($user != null){
    MailController::sendSignupEmail($user->name, $user->email, $user->verification_code);
    return redirect()->back()->with(session()->flash('alert-success', 'Check email for verification link'));
    //Mail::to($user->email)->send(new WelcomeEmail());
    }

    return response()->json([
        'message'=>'Registration successful',
        'data'=>$user
    ],200);
    
    }

//function for user login
    public function userlogin(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'=>'Login failed Email and Password required',
                'error'=>$validator->errors()
            ],422);
        }

        $user=IndividualAccount::where('email',$request->email)->first();

        if($user){
            if(Hash::check($request->password,$user->password)){
                $token=$user->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'message'=>'Login Successful',
                    'token'=>$token,
                    'data'=>$user
                ],200);

            }else{
                return response()->json([
                    'message'=>'Incorrect Credentials',
                ],400);

            }
        }else{
            return response()->json([
                'message'=>'Incorrect Credentials',
            ],400);

        }
     }
     //function to fetch user data with bearer tokens
        public function user(Request $request){
            return response()->json([
                'message'=>'User successfully fetched',
                'data'=>$request->user()
            ],200);
        }

    //function to logout
        public function logout(Request $request){
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message'=>'User logged out',

            ],200);
        }


}


