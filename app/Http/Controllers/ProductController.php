<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Mail\ProductAddEmail;
use App\Mail\productRestockEmail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ProductController extends Controller
{



    //

    //public static function IdGenerator($model, $trow, $lenght = 4, $prefix){};


    //function for adding products
    public function addProduct(Request $request)



    {
        // $user = Auth::user();
        $seller = $request->user();
        if ($seller) {
            $firstname = $seller->firstname;
            $lastname = $seller->lastname;
            $sellerEmail = $seller->email;
            //$productId = IdGenerator::generate(['table' => 'Product','field'=>'productId','length' => 6, 'prefix' =>'AGN']);
            $productId = 'AGP' . rand(100000, 999999);

            $validator = Validator::make($request->all(), [
                //'productId' => 'required|min:2|max:100',
                'product_name' => 'required|min:2|max:100',
                'product_category' => 'required|min:2|max:100',
                'selling_price' => 'required|min:2|max:100',
                'cost_price' => 'required|min:2|max:100',
                'quantityin_stock' => 'required|min:1|max:100',
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
            // Calculate the additional 0.02% to the prices
            $adjustmentFactor = 1 + (0.02 / 100);
            $adjustedSellingPrice = $request->selling_price * $adjustmentFactor;
            $adjustedCostPrice = $request->cost_price * $adjustmentFactor;
            //$product_imagename = time() . '.' . $request->product_image->extension();
            //$request->product_image->move(public_path('/uploads/product_images'), $product_imagename);
            // $productId = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            //$productId = IdGenerator::generate($config);
            //$productId = IdGenerator::generate(['products' => 'products', 'length' => 10, 'prefix' =>'AGN-']);

            $product = Product::create([

                //str_rand(6 only digit)->unique;
                //'productId' => $request->productId = str_rand(6 only digit)->unique;



                'productId' => $productId,
                'sellerId' => $request->user()->sellerId,
                'product_name' => $request->product_name,
                'product_category' => $request->product_category,
                'categoryID' => $categoryID,
                'selling_price' => $adjustedSellingPrice,
                'cost_price' => $adjustedCostPrice,
                'quantityin_stock' => $request->quantityin_stock,
                'unit' => $request->unit,
                'product_description' => $request->product_description,
                'product_image' => $imageName,
                'is_active' => 1
                //'password'=>Hash::make($request->password)
                //'confirm_password'=>'required|same:password'

            ]);

            // Extract image URLs for the product
            $imageURLs = [];
            foreach (explode(',', $product->product_image) as $image) {
                $imageURLs[] = asset('uploads/product_images/' . $image);
            }

            // Add image URLs to the product object
            $product->image_urls = $imageURLs;

            $product->load('sellers:sellerId', 'products');
            //$userEmail = IndividualAccount::find($request->user()->id)->email;

            // Mail::to($userEmail)->send(new ProductAddEmail($product));

            Mail::to($sellerEmail)->send(new ProductAddEmail($product, $product, $firstname, $product->product_name, $product->quantityin_stock,$product->productId));
            return response()->json([
                'message' => 'Product Successfully added',
                'data' => $product
            ], 200);
        } // end of function for adding products
    }

    //Function to view products by id begin
    public function viewProduct(Request $request, string $productId)
    {
        // Find the product by its ID

        //$perPage = $request->input('perPage', 10);

        $product = Product::where('productId', $productId)->first();

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
    }   //Function to view products ends   //Function to view products ends


    //view product details
    public function productDetails(Request $request, string $productId)
    {
        try {
            // Find the product by its ID
            $product = Product::where('productId', $productId)->first();

            // Check if the product exists
            if ($product) {
                // Find the category by categoryID from the product
                $category = Category::where('categoryID', $product->categoryID)->first();

                // Extract image URLs for the product
                $imageURLs = [];
                foreach (explode(',', $product->product_image) as $image) {
                    $imageURLs[] = asset('uploads/product_images/' . $image);
                }

                // Add image URLs and category name to the product object
                $product->image_urls = $imageURLs;
                $product->category_name = $category ? $category->categoryName : null;

                // Fetch the seller's name and address
                $seller = Seller::where('sellerId', $product->sellerId)->first();
                $sellerName = $seller ? $seller->firstname : null; // Adjust according to your Seller model
                $sellerAddress = $seller ? $seller->state : null;  // Adjust according to your Seller model

                return response()->json([
                    'message' => 'Product found.',
                    'data' => [
                        'product' => $product,
                        'seller Info' => [
                            'name' => $sellerName,
                            'address' => $sellerAddress,
                        ]
                    ]
                ], 200);
            } else {
                // If the product is not found, return a 404 error message
                return response()->json([
                    'message' => 'Product not found.',
                ], 404);
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'message' => 'Error occurred while fetching product details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }





    // Function to fetch all products
   // Function to fetch all products
   public function allProducts()
   {
       // Retrieve all products from the database
       $products = Product::orderByDesc('id')->get();

       // Check if any products exist
       if ($products->isEmpty()) {
           return response()->json([
               'message' => 'No products found.',
           ], 404);
       }

       // Iterate through each product to fetch its images
       foreach ($products as $product) {
           // Extract image URLs for the product
           $imageURLs = [];
           if (!empty($product->product_image)) {
               foreach (explode(',', $product->product_image) as $image) {
                   $imageURLs[] = asset('uploads/product_images/' . $image);
               }
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




    public function searchProducts(Request $request)
{
    // Retrieve the search query from query parameters
    //$perPage = $request->input('per_page', 10);

    $search_query = $request->query('search_query', null);

    // Initialize the query builder for the Product model
    $query = Product::query();

    // Search for products with names containing the given substring
    if ($search_query !== null) {
        $query->where(function ($query) use ($search_query) {
            $query->where('productId', $search_query)
                  ->orWhere('product_name', 'like', '%' . $search_query . '%');
        });
    }

    // Execute the query and get the results
    $products = $query->get();

    // Check if any products were found
    if ($products->isEmpty()) {
        return response()->json([
            'message' => 'No products found matching the search criteria.',
        ], 404);
    } else {
        // Iterate through each product to fetch its images
        foreach ($products as $product) {
            // Extract image URLs for the product
            $imageURLs = [];
            if (!empty($product->product_image)) {
                foreach (explode(',', $product->product_image) as $image) {
                    $imageURLs[] = asset('uploads/product_images/' . $image);
                }
            }

            // Add image URLs to the product object
            $product->image_urls = $imageURLs;
        }

        return response()->json([
            'message' => 'Product found.',
            'data' => $products
        ], 200);
    }
}







   /* public function searchProducts($search_query = null)
    {
        $query = Product::query();
        // Search for products with names containing the given substring
        if ($search_query !== null) {
            $query->where(function ($query) use ($search_query) {
                $query->where('productId', $search_query)
                    ->orWhere('product_name', 'like', '%' . $search_query . '%');
            });
        }
        $products = $query->get();

        // Check if any products were found
        if ($products->isEmpty()) {
            return response()->json([
                'message' => 'No products found matching the search criteria.',

            ], 404);
        } else {
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
                'message' => 'Product found.',
                'data' => $products
            ], 200);
        }
    }*/

    //Function to view products ends


    // Function to add a product to the cart


    //Function to delete product

    public function deleteProduct(string $productId, Request $request)
    {
        try {
            // Find the product in the database
            $product = Product::where('productId', $productId)->first();

            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }

            // Check if the authenticated user is the owner of the product
            if ($request->user()->userID != $product->user_id) {
                return response()->json([
                    'message' => 'You are not authorized to delete this product',
                ], 403);
            }

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

            // Delete the product
            $product->delete();

            return response()->json([
                'message' => 'Product Deleted Successfully',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'message' => 'Error deleting product',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    }



    /*public function deleteproduct(string $productId)
    {
        try {
            // Find the product in the database
            $product = Product::where('productId', $productId)->first();
    
            // Check if the product exists
            if (!$product) {
                return response()->json([
                    'message' => 'Product not found',
                ], 404);
            }
    
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
    
            // Delete the product
            $product->delete();
    
            return response()->json([
                'message' => 'Product Deleted Successfully',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion process
            return response()->json([
                'message' => 'Error deleting product',
                'error' => $e->getMessage(), // Include the error message for debugging
            ], 500);
        }
    } 8*/
    // End product delete method




    //Function to edit product
    public function editProduct(Request $request, string $productId)
    {
        // Find the product by its ID
        $product = Product::where('productId', $productId)->first();

        // Check if the product exists
        if (!$product) {
            // If the product is not found, return a 404 error message
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // Check if the authenticated user is the owner of the product
        if ($request->user()->sellerId != $product->sellerId) {
            // If the user is not the owner, return an error message
            return response()->json([
                'message' => 'You are not authorized to edit this product.',
            ], 403);
        }

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
                if ($product->product_image) {
                    // Delete the old product image file
                    if (file_exists(public_path('uploads/product_images/' . $product->product_image))) {
                        unlink(public_path('uploads/product_images/' . $product->product_image));
                    }
                }
                // Move and store the new product image file
                $product_image->move(public_path('/uploads/product_images'), $new_imageName);
                $imageName .= $new_imageName . ",";
            }
            $data['product_image'] = $imageName;
        }

        // Update the product
        $product->update($data);

        // Fetch the updated product with image URLs
        $product = Product::where('productId', $productId)->first();
        $imageURLs = [];
        foreach (explode(',', $product->product_image) as $image) {
            $imageURLs[] = asset('uploads/product_images/' . $image);
        }
        $product->image_urls = $imageURLs;

        // Return success response with the updated product data
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product,
        ], 200);
    }
    //function ends for edit product



    //Function to restock product
    public function restockProduct(Request $request, string $productId)
    {
        // Find the product by its ID
        $product = Product::where('productId', $productId)->first();

        // Check if the product exists
        if ($product) {
            // Check if the authenticated user is the owner of the product
            if ($request->user()->sellerId == $product->sellerId) {
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

                Mail::to($request->email)->send(new productRestockEmail($product, $product, $request->firstname, $product->product_name, $product->quantityin_stock));
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
    public function toggleProductState(Request $request, string $productId)
    {
        // Find the product by its ID
        $product = Product::where('productId', $productId)->first();

        // Check if the product exists
        if ($product) {
            // Check if the authenticated user is the owner of the product
            if ($request->user()->sellerId == $product->sellerId) {
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

    //Hot deals method
    public function hotDeals(Request $request)

    //$perPage = $request->input('per_page', 10);
    {
        try {
            // Calculate the timestamp for 48 hours ago
            $fortyEightHoursAgo = now()->subHours(48);

            // Query to fetch hot deals (newly added products in the last 48 hours)
            $hotDeals = Product::where('created_at', '>=', $fortyEightHoursAgo)
                ->orderByDesc('created_at')
                ->limit(5) // Limit to the top 5 hot deals
                ->get();

            // Iterate over each product to append full image path
            foreach ($hotDeals as $deal) {
                $deal->full_image_path = asset('uploads/product_images/' . $deal->product_image);
            }

            // Return response with hot deals
            return response()->json([
                'message' => 'Hot deals fetched successfully.',
                'hot_deals' => $hotDeals,
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'message' => 'Error occurred while fetching hot deals.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //Method for Popular products

    public function popularProduct(Request $request)
    {
        try {
            // Query to fetch popular products based on the number of times added to the cart
            $popularProduct = Product::selectRaw('products.*, COUNT(carts.id) as cart_count')
                ->leftJoin('carts', 'products.id', '=', 'carts.productId')
                ->groupBy('products.id')
                ->orderByDesc('cart_count')
                ->limit(5) // Limit to the top 5 popular products
                ->get();

            // Iterate over each product to append full image path
            foreach ($popularProduct as $product) {
                $product->full_image_path = asset('uploads/product_images/' . $product->product_image);
            }

            // Return response with popular products
            return response()->json([
                'message' => 'Popular products fetched successfully.',
                'popular_products' => $popularProduct,
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'message' => 'Error occurred while fetching popular products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


   



}