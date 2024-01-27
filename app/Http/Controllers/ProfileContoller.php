<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileContoller extends Controller
{
    //Begin function to change password

    public function change_password(Request $request){
        $validator = Validator::make($request->all(), [
            'old_password'=>'required',
            'password'=>'required|min:6|max:100',
            'confirm_password'=>'required|same:password'
        ]);
        if ($validator->fails()){
            return response()->json([
                'message'=>'Validations failed',
                'error'=>$validator->errors()
            ],422);
        }
        $user=$request->user();
        if(Hash::check($request->old_password,$user->password)){
            $user->update([
                'password'=>Hash::make($request->password)
            ]);
            return response()->json([
                'message'=>'Password changed'
            ],200);

        }else{
            return response()->json([
                'message'=>'Old password does not match'
            ],400);
        }
    }// End function to change password

    // Begin profile update function
    public function update_profile(Request $request){
        $validator = Validator::make($request->all(),[
            'profile_photo'=> 'nullable|image|mimes:jpg,png,bmp'

        ]);
        if ($validator->fails()){
            return response()->json([
                'message'=>'validations fails',
                'errors'=>$validator->errors()
            ],422);
        }
        $user=$request->user();
        if($request->hasFile('profile_photo')){
            if($user->profile_photo){
                $old_path=public_path().'uploads/profile_images/'.$user->profile_photo;
                if(File::exists($old_path)){
                    File::delete($old_path);
                }

            }
            $image_name = 'profile-image-'.time().'.'.$request->
                profile_photo->extension();
                $request->profile_photo->move(public_path('/uploads/profile_images'),$image_name);
        }
    }// End profile update function
}
