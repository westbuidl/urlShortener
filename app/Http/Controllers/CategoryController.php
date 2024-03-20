<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    // Method to display all categories
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Method to create a new category
    public function addCategory(Request $request)
    {

        $categoryID = 'AGC' . rand(000, 999);

        $validator = Validator::make($request->all(), [
            //'categoryID' => 'required|string|max:255',
            'categoryName' => 'required|string|max:255|unique:categories',
            'categoryDescription' => 'required|string|max:255',
            'categoryImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            //'categoryImage.*' => 'required|array|max:255',
            'quantity_instock' => 'required|string|max:255',
            'quantity_sold' => 'required|string|max:255',
            // Add more validation rules as needed
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validations fails',
                'error' => $validator->errors()
            ], 422);
        }

        $new_imageName = time() . '.' .  $request->categoryImage->extension();

        // Move the image to the desired location
        $request->categoryImage->move(public_path('/uploads/category_image'), $new_imageName);

        $category = Category::create([
            'categoryID' => $categoryID,
            'categoryName' => $request->categoryName,
            'categoryDescription' => $request->categoryDescription,
            'categoryImage' => $new_imageName,
            'quantity_instock' => $request->quantity_instock,
            'quantity_sold' => $request->quantity_sold,

            // Add more fields as needed
        ]);

        return response()->json([
            'message' => 'Category Successfully added',
            'data' => $category
        ], 200);

        // return response()->json($category, 201);
    }

    // Method to display a specific category
    public function viewAllcategory()
    {
        try {
            $categories = Category::all();
            $categories = Category::orderByDesc('id')->get();

            // Fetch image path for each category
            foreach ($categories as $category) {
                $imageURLs = [];
                foreach (explode(',', $category->categoryImage) as $image) {
                    $imageURLs[] = asset('uploads/category_image/' . $image);
                }

                $category->image_urls = $imageURLs;
            }

            return response()->json([
                'message' => 'All categories fetched successfully.',
                'categories' => $categories,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while fetching categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Method to display a specific category
    public function viewCategory(Request $request, string $categoryID)
    {
        try {
            // Find the category by the user-defined categoryID

            $category = Category::where('categoryID', $categoryID)->first();

            // Check if the category exists
            if ($category) {
                // Fetch image path for the category
                if ($category->image) {
                    $category->image_path = asset('uploads/category_image/' . $category->image);
                }

                return response()->json([
                    'message' => 'Category fetched successfully.',
                    'category' => $category,
                ], 200);
            } else {
                // If the category is not found, return a 404 response
                return response()->json([
                    'message' => 'Category not found.',
                ], 404);
            }
        } catch (\Exception $e) {
            // Handle any other exceptions
            return response()->json([
                'message' => 'Error occurred while fetching the category.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //method to display category details
    public function categoryDetails(Request $request, string $categoryID)
    {
        try {
            // Find the category by ID
            $category = Category::where('categoryID', $categoryID)->first();

            // Fetch all products in the category
           
            $products = Products::where('categoryID', $category->categoryID)->get();
            $products = Products::orderByDesc('id')->get();

            return response()->json([
                'message' => 'Category details fetched successfully.',
                'category_name' => $category->categoryName,
                'products' => $products,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error occurred while fetching category details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    //End

    // Method to update a category
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            // Add more validation rules as needed
        ]);

        $category->update([
            'name' => $request->name,
            // Add more fields as needed
        ]);

        return response()->json($category, 200);
    }

    // Method to delete a category
    public function deleteCategory(string $id)
    {
        // Find the category by ID
        $category = Category::find($id);

        // If the category exists
        if ($category) {
            // Check if the category has an associated image
            if ($category->categoryImage) {
                // Delete the category image from the image folder
                $imagePath = public_path('uploads/category_image/') . $category->categoryImage;
                if (File::exists($imagePath)) {
                    File::delete($imagePath);
                }
            }

            // Delete the category
            $category->delete();

            // Return success response
            return response()->json([
                'message' => 'Category deleted successfully',
            ], 200);
        } else {
            // If the category does not exist, return a not found response
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }
    }
}
