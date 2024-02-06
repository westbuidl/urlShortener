<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\IndividualAccount;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Validator;
use App\Notifications\EmailVerificationNotification;
use App\Mail\SignupEmail;

class UserController extends Controller
{
    //function for user registration
    public function individual(Request $request){
        $userID = 'AGU-'.rand(00000000, 99999999);
        $verification_code = rand(000000, 999999);
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

   $individualuser=IndividualAccount::create([
             'userID'=>$userID, 
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
             'verification_code' => $verification_code
             //$user->verification_code= sha1(time());
            //'confirm_password'=>'required|same:password'

           // $individualuser->notify(new EmailVerificationNotification())

            
   ]);
   //send email after registration
  /* if($individualuser != null){
    MailController::sendSignupEmail($individualuser->firstname, $individualuser->email, $individualuser->verification_code);
    return redirect()->back()->with(session()->flash('alert-success', 'Check email for verification link'));
    //Mail::to($user->email)->send(new WelcomeEmail());
    }*/

    Mail::to($request->email)->send(new SignupEmail($individualuser));

    return response()->json([
        'message'=>'Registration successful Verification Email Sent',
        'data'=>$individualuser
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

        $individualuser=IndividualAccount::where('email',$request->email)->first();

        if($individualuser){
            if(Hash::check($request->password,$individualuser->password)){
                $token=$individualuser->createToken('auth-token')->plainTextToken;

                return response()->json([
                    'message'=>'Login Successful',
                    'token'=>$token,
                    'data'=>$individualuser
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
        public function individualuser(Request $request){
            return response()->json([
                'message'=>'User successfully fetched',
                'data'=>$request->individualuser()
            ],200);
        }

    //function to logout
        public function logout(Request $request){
            $request->individualuser()->currentAccessToken()->delete();
            return response()->json([
                'message'=>'User logged out',

            ],200);
        }

        //function to check and verify email
        public function verifymail(Request $request){

            $validator = Validator::make($request->all(),[
                'otp'=>'required',
                //'password'=>'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'message'=>'OTP is required to proceed',
                    'error'=>$validator->errors()
                ],422);
            }

            $emailverify=IndividualAccount::where('email',$request->email)->first();

            if($emailverify){
                if(Hash::check($request->password,$emailverify->password)){
                    $token=$emailverify->createToken('auth-token')->plainTextToken;
    
                    return response()->json([
                        'message'=>'Email Verified proceed to login',
                        //'token'=>$token,
                        //'data'=>$emailverify
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


        //function for email verification
       /* public function sendVerifyMail($email){
            if(auth()->user()){
                $individualuser = IndividualAccount::where('email', $email)->get();
                if(count($individualuser) > 0){

                    return $individualuser[0]['id'];

                    $random = Str::random(40);
                    $domain = URL::to('/');
                    $url = $domain.'/'.$random;

                    $data['url'] = $url;
                    $data['email'] = $email;
                    $data['title'] = "Email verification";
                    $data['body'] = "Enter code to verify";

                    Mail::send('verifyMail',['data'=>$data],function($message) use ($data){
                        $message->to($data['email'])->subject($data['title']);

                    });

                    $individualuser = IndividualAccount::find($individualuser[0]['id']);
                    $individualuser->remember_token = $random;
                    $individualuser -> save();

                    return response()->json(['message'=>'Mail sent successfully',],200);
                    


                }else{
                    return response()->json([
                        'message'=>'user not found',
        
                    ],400);

                }

            }
            else{

                return response()->json([
                    'message'=>'Email not verified',
    
                ],400);
            }
        }*/
}


