<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Support\Facades\Validator;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth');
    }

    public function create()
    {
        $validator = Validator::make(request()->all(), [
            'file' => 'required|mimes:mp4,mov,ogg,jpeg,png,pdf,mpeg,mpga,mp3,wav|max:15936768',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        if (request()->hasFile('file') && request()->get('type')) {
            $path = request()->file('file')->store('');

            $resource = Resource::create([
                'path' => $path,
                'type' => request()->get('type')
            ]);

            return response()->json($resource);
        } else {
            return response()->json(['message' => 'File and Type is required']);
        }
    }
}
