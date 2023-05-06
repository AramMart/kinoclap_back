<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.custom_auth');
    }


    public function indexAdminNotApproved()
    {
        $users = User::where('type', 'user')->where('approved', 'PENDING')->paginate(10);
        return response()->json($users);
    }

    public function singleAdminNotApproved($id)
    {
        $user = User::with(
            ['profile', 'profile.country','profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']
        )->find($id);

        return response()->json($user);
    }

    public function index()
    {
        $users = User::where('type', 'user')->paginate(10);
        return response()->json($users);
    }

    public function single($id)
    {
        $user = User::find($id);
        return response()->json($user);
    }

}
