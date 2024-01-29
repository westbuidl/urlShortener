<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    //
    //function for user registration
    public function addproduct(Request $request){
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
             'product_id'=>'required|min:2|max:100',
             'product_name'=>'required|min:2|max:100',
             'product_category'=>'required|min:2|max:100',
             'selling_price'=>'required|min:2|max:100',
             'cost_price'=>'required|min:2|max:100',
             'quantityin_stock'=>'required|min:2|max:100',
             'unit'=>'required|min:2|max:100',
             'product_description'=>'required|min:2|max:255',
             'product_image'=>'required|image|mimes:jpg,png,bmp'
             //'password'=>'required|min:6|max:100',
             //'confirm_password'=>'required|same:password'
 
         ]);
        if ($validator->fails()) {
            return response()->json([
                'message'=>'Validations fails',
                'error'=>$validator->errors()
            ],422);
        }
    
        $product_imagename=time().'.'.$request->product_image->extension();
        $request->product_image->move(public_path('/uploads/product_images'),$product_imagename);
    
        $product=Products::create([
             'product_id'=>$request->product_id,
             'user_id'=>$request->user()->id,
             'product_name'=>$request->product_name,
             'product_category'=>$request->product_category,
             'selling_price'=>$request->selling_price,
             'cost_price'=>$request->cost_price,
             'quantityin_stock'=>$request->quantityin_stock,
             'unit'=>$request->unit,
             'product_description'=>$request->product_description,
             'product_image'=>$product_imagename
             //'password'=>Hash::make($request->password)
             //'confirm_password'=>'required|same:password'
    ]);

       
    $product->load('individuals:user_id','products');
     return response()->json([
         'message'=>'Product Successfully added',
         'data'=>$product
     ],200);
     
     }

     /* Begin profile picture update function
    public function update_profile(Request $request)
    {
       
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
            'message' => 'Profile Picture successfully updated',

        ], 200);
    } // End profile update function*/

}
