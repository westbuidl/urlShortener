<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;
use App\Mail\ProductAddEmail;
use App\Mail\Productrestockemail;
use App\Models\IndividualAccount;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        // $user = Auth::user();
        $user = $request->user();
        if ($user) {
            $firstname = $user->firstname;
            $lastname = $user->lastname;
            $userEmail = $user->email;
            //$product_id = IdGenerator::generate(['table' => 'Products','field'=>'product_id','length' => 6, 'prefix' =>'AGN']);
            $product_id = 'AGP' . rand(000000, 999999);

            $validator = Validator::make($request->all(), [
                //'product_id' => 'required|min:2|max:100',
                'product_name' => 'required|min:2|max:100',
                'product_category' => 'required|min:2|max:100',
                'selling_price' => 'required|min:2|max:100',
                'cost_price' => 'required|min:2|max:100',
                'quantityin_stock' => 'required|min:2|max:100',
                'unit' => 'required|min:1|max:100',
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

            // Check if the category exists
            $category = Category::where('categoryName', $request->product_category)->first();

            if (!$category) {
                // If the category does not exist, return an error response
                return response()->json([
                    'message' => 'Category does not exist',
                ], 404);
            }

            // Category exists, get its ID
            $categoryID = $category->categoryID;




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
                'user_id' => $request->user()->userID,
                'product_name' => $request->product_name,
                'product_category' => $request->product_category,
                'categoryID' => $categoryID,
                'selling_price' => $request->selling_price,
                'cost_price' => $request->cost_price,
                'quantityin_stock' => $request->quantityin_stock,
                'unit' => $request->unit,
                'product_description' => $request->product_description,
                'product_image' => $imageName,
                'is_active' => 1
                //'password'=>Hash::make($request->password)
                //'confirm_password'=>'required|same:password'

            ]);



            $product->load('individuals:userID', 'products');
            //$userEmail = IndividualAccount::find($request->user()->id)->email;

            // Mail::to($userEmail)->send(new ProductAddEmail($product));

            Mail::to($userEmail)->send(new ProductAddEmail($product, $product, $firstname));
            return response()->json([
                'message' => 'Product Successfully added',
                'data' => $product
            ], 200);
        } // end of function for adding products
    }

    //Function to view products by id begin
    public function viewproduct(Request $request, string $product_id)
    {
        // Find the product by its ID
        $product = Products::where('product_id', $product_id)->first();

        // Check if the product exists
        if ($product) {
            // Check if the authenticated user is the owner of the product
            if ($request->user() && $request->user()->userID == $product->user_id) {
                // If the user is the owner, return the product data

                // Extract image URLs for the product
                $imageURLs = [];
                foreach (explode(',', $product->product_image) as $image) {
                    $imageURLs[] = asset('uploads/product_images/' . $image);
                }

                // Add image URLs to the product object
                $product->image_urls = $imageURLs;

                return response()->json([
                    'message' => 'Product found.',
                    'data' => [
                        'product' => $product,
                    ]
                ], 200);
            } else {
                // If the user is not the owner, return an error message
                return response()->json([
                    'message' => 'You are not authorized to view this product.',
                ], 403);
            }
        } else {
            // If the product is not found, return a 404 error message
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }   //Function to view products ends

    // Function to fetch all products
    public function allProducts()
    {
        // Retrieve all products from the database
        $products = Products::orderByDesc('product_id')->get();
        // Iterate through each product to fetch its images
        foreach ($products as $product) {
            // Extract image URLs for the product
            $imageURLs = [];
            foreach (explode(',', $product->product_image) as $image) {
                $imageURLs[] = asset('uploads/product_images/' . $image);
            }

            // Add image URLs to the product object
            $product->image_urls = $imageURLs;
        }

        return response()->json([
            'message' => 'All products fetched successfully.',
            'data' => [
                'product' => $products,

            ]
        ], 200);
    }



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


    // Function to add a product to the cart


    //Function to delete product
    public function deleteproduct(string $product_id)

    {
        try {
            //find prtoduct in the database
            //$product = Products::find($product_id);
            $product = Products::where('product_id', $product_id);
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
                    $imagePath = public_path('/uploads/product_images/' . $filename);
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



    //Function to edit product
    public function editproduct(Request $request, string $product_id)
    {
        // Find the product by its ID
        $product = Products::where('product_id', $product_id)->first();

        // Check if the product exists
        if ($product) {
            // Check if the authenticated user is the owner of the product
            if ($request->user()->userID == $product->user_id) {
                // Validate the request data
                $validator = Validator::make($request->all(), [
                    'new_product_name' => 'required|min:2|max:100',
                    'new_product_category' => 'required|min:2|max:100',
                    'new_selling_price' => 'required|min:2|max:100',
                    'new_cost_price' => 'required|min:2|max:100',
                    'new_quantityin_stock' => 'required|min:2|max:100',
                    'new_unit' => 'required|min:2|max:100',
                    'new_product_description' => 'required|min:2|max:255',
                    'product_image' => 'array|min:2|max:5',
                    'product_image.*' => 'image|mimes:jpg,png,bmp'
                ]);

                // If validation fails, return error response
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation fails',
                        'error' => $validator->errors()
                    ], 422);
                }

                 // Check if the category exists
        $category = Category::where('categoryName', $request->new_product_category)->first();

        if (!$category) {
            // If the category does not exist, return an error response
            return response()->json([
                'message' => 'Category does not exist',
            ], 404);
        }

        // Category exists, get its ID
        $categoryID = $category->categoryID;

                // Update product details
                $data = [
                    'product_name' => $request->new_product_name,
                    'product_category' => $request->new_product_category,
                    'selling_price' => $request->new_selling_price,
                    'categoryID' => $categoryID,
                    'cost_price' => $request->new_cost_price,
                    'quantityin_stock' => $request->new_quantityin_stock,
                    'unit' => $request->new_unit,
                    'product_description' => $request->new_product_description
                ];

                // Handle product image updates
                if ($request->hasFile('product_image')) {
                    $imageName = '';
                    foreach ($request->file('product_image') as $product_image) {
                        $new_imageName = rand() . '.' . $product_image->getClientOriginalExtension();
                        $product_image->move(public_path('/uploads/product_images'), $new_imageName);
                        $imageName .= $new_imageName . ",";
                    }
                    $data['product_image'] = $imageName;
                }

                // Update the product
                $product->update($data);

                // Return success response
                return response()->json([
                    'message' => 'Product updated successfully',
                ], 200);
            } else {
                // If the user is not the owner, return an error message
                return response()->json([
                    'message' => 'You are not authorized to edit this product.',
                ], 403);
            }
        } else {
            // If the product is not found, return a 404 error message
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    } //function ends for edit product



    //Function to restock product
    public function restockproduct(Request $request, string $product_id)
    {
        // Find the product by its ID
        $product = Products::where('product_id', $product_id)->first();

        // Check if the product exists
        if ($product) {
            // Check if the authenticated user is the owner of the product
            if ($request->user()->userID == $product->user_id) {
                // Validate the request data
                $validator = Validator::make($request->all(), [
                    'new_quantityin_stock' => 'required|min:2|max:100',
                    'new_selling_price' => 'required|min:2|max:100',
                    'new_cost_price' => 'required|min:2|max:100',
                ]);

                // If validation fails, return error response
                if ($validator->fails()) {
                    return response()->json([
                        'message' => 'Validation fails',
                        'error' => $validator->errors()
                    ], 422);
                }

                // Update product details
                $product->update([
                    'quantityin_stock' => $request->new_quantityin_stock,
                    'selling_price' => $request->new_selling_price,
                    'cost_price' => $request->new_cost_price,
                ]);

                // Return success response
                return response()->json([
                    'message' => 'Restock successful',
                ], 200);
            } else {
                // If the user is not the owner, return an error message
                return response()->json([
                    'message' => 'You are not authorized to restock this product.',
                ], 403);
            }
        } else {
            // If the product is not found, return a 404 error message
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }


    //function ends for edit product



    // Function to toggle product state (active/inactive)
    public function toggleProductState(Request $request, string $product_id)
    {
        // Find the product by its ID
        $product = Products::where('product_id', $product_id)->first();

        // Check if the product exists
        if ($product) {
            // Check if the authenticated user is the owner of the product
            if ($request->user()->id == $product->user_id) {
                // Toggle the product state
                $product->update([
                    'is_active' => !$product->is_active,
                ]);

                // Return success response with updated product state
                return response()->json([
                    'message' => 'Product state updated successfully',
                    'is_active' => $product->is_active,
                ], 200);
            } else {
                // If the user is not the owner, return an error message
                return response()->json([
                    'message' => 'You are not authorized to update the state of this product.',
                ], 403);
            }
        } else {
            // If the product is not found, return a 404 error message
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }
    }
    //function ends for edit product
}
/*Categories*
1.Fresh Fruits
Eg: apple, pineapple,tomato 
2.Fresh Vegetables
Eg: greens, ugu, scent leaf
3.Processed fruits and veggies
Eg: juices and chopped veggies
4.Cereals and beverages
Eg: fruit mix cereals, Ovaltine, drinks made with cocoa
5.Nuts and seeds
Eg: almond, palm nut, sesame seed
6.Proteins
Eg: all kinds of meat, fish, poultry, ukwa
7. Processed foods
Eg: tin tomato, baked beans, canned corn, dried foods like fish, dried cocoyam( achicha), frozen foods 
8.Cooking
Eg: spices, oil
9.Roots and tubers
Eg: potatoes, yam, cocoyam, sugar beets,ginger
10.Grains
Eg: rice, wheat, oats, millet 
11.Cosmetics and Pharmaceuticals
Eg: herbal drugs, herbal dental care, herbal facial cleansers
12.Rubber and textile
Eg: latex, cotton, plastic, leather 
13. Paper and paper products
Eg: paper towels, paper cups, tissue paper 
14.agro-chemicals
Eg: fertilizers, pesticides, vaccines for animals 
15.Snacks and pastries
Eg: biscuits, chocolate,sweets, all things baked
16. Wood and wood products
Eg: plywood, mahogany, chairs,table
17. Dairy and dairy products
Eg: milk, yogurt,cheese, butter, custard,cream
18. Baking ingredients 
Eg: baking soda, vinegar and things used in baking
19. Diabetics
Eg: packaged products that don't contain a whole lot of sugar you know specifically for diabetics ( you know to make it easy to find)
20. lactose intolerant
Eg: yoghurt, foods that don't contain dairy and their products 
21.Drinks( I'm not sure about this, I don't know if things like packaged palm wine, beer,burukutu and sauerkraut,wines can be available but anyway if you think it won't make sense then let it go)


21 is asterisked*/