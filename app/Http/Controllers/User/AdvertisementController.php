<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\News;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdvertisementController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => []]);
    }

    public function index(Request $request)
    {
        try {
            $types = $request->get('types');

            $advertisements = Advertisement::with('resources')
                ->where('active', true)
                ->whereIn('type', $types)->get();

            return response()->json($advertisements);
        } catch (\Exception $exception) {
            return response()->json([], 500);
        }
    }

    public function single($id)
    {
        $advertisement = Advertisement::with('resources')->find($id);
        return response()->json(['data' => $advertisement], 200);
    }


    public function create()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3',
            'type' => 'required|in:suggest,search',
            'sub_category_id' => 'required|numeric',
            'resources' => 'required|array',
            'resources.*' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages(), 'data' => null], 400);
        }

        $resources = Resource::find(request()->get('resources'));
        $advertisement = Advertisement::create($validator->validate());
        $advertisement->resources()->attach($resources);

        if ($advertisement->id) {
            return response()->json(['message' => 'Advertisement Created', 'data' => $advertisement], 200);
        }
        return response()->json(['message' => 'Advertisement not created.', 'data' => null], 400);
    }


    public function delete($id)
    {
        $deleted = Advertisement::destroy($id);
        return response()->json(['message' => $deleted ? 'Advertisement deleted successfully' : 'Something went wrong!']);
    }
}
