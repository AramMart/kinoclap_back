<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['index']]);
    }

    public function index(Request $request)
    {
        $type = $request->get('type');
        if (!in_array($type, ['user','guest', 'all']) || (!auth()->user() && $type === 'user')) {
            return response()->json(['message' => 'Invalid type']);
        }

        $categories = Category::with('subCategories')->where('type', $type)->get();
        return response()->json(['data'=> $categories],200);
    }

    public function indexAdmin()
    {
        $categories = Category::paginate(10);
        return response()->json($categories);
    }

    public function single($id)
    {
        $category = Category::with('subCategories')->find($id);
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
