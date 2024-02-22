<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use App\Mail\ProductAddEmail;
use App\Http\Controllers\Controller;
use App\Models\IndividualAccount;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ProductController extends Controller
{



    //

    //public static function IdGenerator($model, $trow, $lenght = 4, $prefix){};


    //function for adding products
    public function addproduct(Request $request)

    {
        //$product_id = IdGenerator::generate(['table' => 'Products','field'=>'product_id','length' => 6, 'prefix' =>'AGN']);
        $product_id = 'AGP' . rand(000000, 999999);

        $validator = Validator::make($request->all(), [
            //'product_id' => 'required|min:2|max:100',
            'product_name' => 'required|min:2|max:100',
            'product_category' => 'required|min:2|max:100',
            'selling_price' => 'required|min:2|max:100',
            'cost_price' => 'required|min:2|max:100',
            'quantityin_stock' => 'required|min:2|max:100',
            'unit' => 'required|min:2|max:100',
            'product_description' => 'required|min:2|max:255',
            'product_image' => 'required|array|min:2|max:5',
            'product_image.*' => 'image|mimes:jpg,png,bmp'
            //'product_image' => 'required|image|mimes:jpg,png,bmp'
            //'password'=>'required|min:6|max:100',
            //'confirm_password'=>'required|same:password'

        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations fails',
                'error' => $validator->errors()
            ], 422);
        }

        $product_image = $request->file('product_image');
        $imageName = '';
        foreach ($product_image as $product_images) {
            $new_imageName = rand() . '.' . $product_images->getClientOriginalExtension();
            $product_images->move(public_path('/uploads/product_images'), $new_imageName);
            $imageName = $imageName . $new_imageName . ",";
        }

        //$product_imagename = time() . '.' . $request->product_image->extension();
        //$request->product_image->move(public_path('/uploads/product_images'), $product_imagename);
        // $product_id = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        //$product_id = IdGenerator::generate($config);
        //$product_id = IdGenerator::generate(['products' => 'products', 'length' => 10, 'prefix' =>'AGN-']);

        $product = Products::create([

            //str_rand(6 only digit)->unique;
            //'product_id' => $request->product_id = str_rand(6 only digit)->unique;



            'product_id' => $product_id,
            'user_id' => $request->user()->id,
            'product_name' => $request->product_name,
            'product_category' => $request->product_category,
            'selling_price' => $request->selling_price,
            'cost_price' => $request->cost_price,
            'quantityin_stock' => $request->quantityin_stock,
            'unit' => $request->unit,
            'product_description' => $request->product_description,
            'product_image' => $imageName,
            'active_state' => 1
            //'password'=>Hash::make($request->password)
            //'confirm_password'=>'required|same:password'

        ]);

        $user = IndividualAccount::find($request->user()->id);
        $userEmail = $user->email;
        $userName = $user->firstname.' '. $user->lastname;

        $product->load('individuals:user_id', 'products');
        $userEmail = IndividualAccount::find($request->user()->id)->email;

        // Mail::to($userEmail)->send(new ProductAddEmail($product));

        Mail::to($userEmail)->send(new ProductAddEmail($product, $userEmail));
        return response()->json([
            'message' => 'Product Successfully added',
            'data' => $product
        ], 200);
    } // end of function for adding products

    //Function to view products begin
    public function viewproduct(string $product_id)
    {
        // Find the product by its ID
        $product = Products::find($product_id);

        // Check if the product exists
        if ($product) {
            return response()->json([
                'message' => 'Product found.',
                'data' => $product
            ], 200);
        } else {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    } //Function to view products ends

    public function searchproducts($search_query = null)
    {
        $query = Products::query();
        // Search for products with names containing the given substring
        if ($search_query !== null) {
            $query->where(function ($query) use ($search_query) {
                $query->where('product_id', $search_query)
                    ->orWhere('product_name', 'like', '%' . $search_query . '%');
            });
        }
        $products = $query->get();
        //$products = Products::where('product_name', 'like', '%' . $product_name . '%')->get();

        // Check if any products were found
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching the search criteria.',
            ], 404);
        } else {
            return response()->json([
                'message' => 'Products found.',
                'data' => $products
            ], 200);
        }
    }
    //Function to view products ends



    //Function to delete product
    public function deleteproduct(string $product_id)

    {
        try {
            //find prtoduct in the database
            $product = Products::find($product_id);
            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }


            // Find the product in the database
            //$product = Products::findOrFail($product_id);

            //$imageFilenames = explode(',', $product->product_image);

            // Check if the product has associated images
            if (!empty($product->product_image)) {
                // Split the comma-separated image filenames into an array
                $imageFilenames = explode(',', $product->product_image);

                // Delete associated images from the filesystem
                foreach ($imageFilenames as $filename) {
                    // Assuming images are stored in a folder named 'product_images'
                    $imagePath = public_path('/uploads/product_images/'.$filename);
                    if (File::exists($imagePath)) {
                        File::delete($imagePath);
                    }
                }
            }

            Products::destroy($product_id);
            return response()->json([
                'message' => 'Product Deleted Successfully',
                //'data' => $product
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'message' => 'Error deleting product',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }
}
