<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['index', 'single']]);
    }

    public function index($id)
    {
        $subCategories = SubCategory::whereHas('category', function ($q) use ($id) {
            $q->where('id', $id);
        })->paginate(10);
        return response()->json($subCategories);
    }

    public function single($id)
    {
        $subCategory = SubCategory::with('category')->find($id);
        return response()->json($subCategory);
    }

    public function create($id)
    {
       try {
           $validator = Validator::make(request()->all(), [
               'title' => 'required|string|min:3|max:255|unique:sub_categories'
           ]);

           if($validator->fails()){
               return response()->json(['message'=> $validator->errors()->first()],400);
           }
           $data = [
               'title' => request()->get('title'),
               'category_id' => $id
           ];

           if($subCategory = SubCategory::create($data)){
               return response()->json(['message' => 'Sub Category Created','data'=> $subCategory],200);
           }
           return response()->json(['message'=> 'Sub Category not created.'],400);
       } catch (\Exception $e) {
           return response()->json(['message'=> 'Sorry something went wrong', 'e' => $e->getMessage()],400);
       }
    }

    public function delete($id)
    {
        $deleted = SubCategory::destroy($id);
        return response()->json(['message' => $deleted ? 'Sub Category deleted successfully': 'Something went wrong!']);
    }
}
