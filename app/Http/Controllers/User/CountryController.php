<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        try {
            return response()->json(Country::all());
        } catch (\Exception $exception) {
            return response()->json([], 500);
        }
    }

    public function indexAdmin()
    {
        $categories = Category::paginate(10);
        return response()->json($categories);
    }

    public function single($id)
    {
        $category = Category::find($id);
        return response()->json($category);
    }

    public function create()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|min:3|max:255|unique:categories',
            'type' => 'required|in:user,guest,all',
        ]);

        if($validator->fails()){
            return response()->json(['message'=> $validator->errors()->first()],400);
        }

        if($news = Category::create($validator->validate())){
            return response()->json(['message' => 'Category Created','data'=> $news],200);
        }
        return response()->json(['message'=> 'Category not created.'],400);
    }


    public function delete($id)
    {
        $deleted = Category::destroy($id);
        return response()->json(['message' => $deleted ? 'Category deleted successfully': 'Something went wrong!']);
    }
}
