<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['index']]);
    }

    public function index(Request $request)
    {
        $types = $request->get('types');
        $validTypes = true;
        for ($i = 0; $i < count($types); $i++) {
            if (!in_array($types[$i], ['user','guest', 'all'])) {
                $validTypes = false;
            }
        }

        if (!$validTypes || (!auth()->user() && in_array('user', $types))) {
            return response()->json(['message' => 'Invalid type'], 422);
        }

        $newses = News::with('resources')
            ->where('active', true)
            ->whereIn('type', $types)->get();

        return response()->json($newses);
    }

    public function indexAdmin()
    {
        $newses = News::with('resources')->paginate(10);
        return response()->json($newses);
    }

    public function single($id)
    {
        $news = News::with('resources')->find($id);
        return response()->json($news,200);
    }


    public function create()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3',
            'type' => 'required|in:user,guest,all',
            'resources' => 'required|array',
            'resources.*' => 'numeric',
        ]);

        if($validator->fails()){
            return response()->json(['message'=> $validator->errors()->first()],400);
        }

        $resources = Resource::find(request()->get('resources'));
        $news = News::create($validator->validate());
        $news->resources()->attach($resources);

        if($news->id) {
            return response()->json(['message' => 'News Created','data'=> $news],200);
        }
        return response()->json(['message'=> 'News not created.','data'=>null],400);
    }


    public function delete($id)
    {
       $deleted = News::destroy($id);
       return response()->json(['message' => $deleted ? 'News deleted successfully': 'Something went wrong!']);
    }

}
