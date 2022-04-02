<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Resource;

class ResourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth');
    }

    public function create()
    {
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
