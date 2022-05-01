<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\News;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['single', 'index']]);
    }

    public function index()
    {
        try {
            $types = request()->get('types');
            $subCategoryId = request()->get('subCategoryId');

            $advertisements = Advertisement::with('resources','user')->where('active', true);

            if ($subCategoryId) {
               $advertisements->whereHas('sub_category',function ($q) use ($subCategoryId) {
                   $q->where('id', $subCategoryId);
               });
            }

            if (!is_null($types) and count($types)) {
                $advertisements->whereIn('type', $types);
            }

            $data = $advertisements->get();

            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json([$exception->getMessage()], 500);
        }
    }

    public function single($id)
    {
        $advertisement = Advertisement::with('resources', 'user', 'sub_category')->where('active', true)->find($id);
        return response()->json($advertisement);
    }


    public function create()
    {
        $user = auth()->user();
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3',
            'type' => 'required|in:suggest,search',
            'sub_category_id' => 'required|numeric',
            'resources' => 'required|array',
            'resources.*' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first() ], 400);
        }

        $resources = Resource::find(request()->get('resources'));

        $advertisement = Advertisement::create(array_merge($validator->validate(), ['user_id' => $user->id]));
        $advertisement->resources()->attach($resources);

        if ($advertisement->id) {
            return response()->json($advertisement);
        }
        return response()->json(['message' => 'Advertisement not created.'], 400);
    }


    public function delete($id)
    {
        $deleted = Advertisement::destroy($id);
        return response()->json(['message' => $deleted ? 'Advertisement deleted successfully' : 'Something went wrong!']);
    }
}
