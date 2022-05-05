<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth');
    }

    public function single()
    {
        $id = auth()->user()->id;
        $user = UserProfile::with(['user', 'resources'])->find($id);
        return response()->json($user);
    }

    public function update()
    {

    }

    public function updateResources()
    {

    }
}
