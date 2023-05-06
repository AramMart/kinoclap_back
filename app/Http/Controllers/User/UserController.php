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
        $users = User::where('type', 'user')->whereHas('profile', function ($q) {
            $q->where('approved', 'PENDING');
        })->paginate(10);

        return response()->json($users);
    }

    public function updateModerationStatus($id)
    {
        if (!in_array(request()->input('status'), ['ACCEPT', 'REJECT'])) {
            return response()->json([], 400);
        }
        $user = User::find($id);

        if (!$user) {
            return response()->json([], 404);
        }

        $user->profile->approved = request()->input('status') === 'ACCEPT' ? 2 : 3;
        $user->profile->save();

        return response()->json();
    }

    public function index()
    {
        $users = User::where('type', 'user')->paginate(10);
        return response()->json($users);
    }

    public function single($id)
    {
        $user = User::with(
            ['profile', 'profile.country','profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']
        )->find($id);

        return response()->json($user);
    }

}
