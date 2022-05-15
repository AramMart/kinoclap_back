<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\News;
use App\Models\Resource;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth');
    }

    public function index()
    {
        $professionId = request()->get('profession_id');
        $search = request()->get('search');
        $profiles = new User();

        if ($professionId) {
            $profiles = $profiles->whereHas('profile', function ($q) use ($professionId) {
                if ($professionId) {
                    $q->where('profession_id', $professionId);
                }
            });
        }

        if ($search) {
            $chunks = explode(" ", $search);
            if (count($chunks) > 1) {
                $profiles = $profiles->where(
                    [['first_name', 'LIKE', "%{$chunks[0]}%"], ['last_name', 'LIKE', "%{$chunks[1]}%"]]
                )->orWhere(
                    [['first_name', 'LIKE', "%{$chunks[1]}%"], ['last_name', 'LIKE', "%{$chunks[0]}%"]]
                );
            } else {
                $profiles = $profiles->where('first_name', 'LIKE', "%{$chunks[0]}%")
                    ->orWhere('last_name', 'LIKE', "%{$chunks[0]}%");
            }
        }

        $profiles = $profiles->with(['profile', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']);

        return response()->json($profiles->get());
    }

    public function single()
    {
        $userId = auth()->user()->id;
        $user = User::with(['profile', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources'])->find($userId);
        return response()->json($user);
    }

    public function singleById($userId)
    {
        $user = User::with(['profile', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources'])->find($userId);
        return response()->json($user);
    }

    public function update()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'description' => 'required|string|min:3',
                'phone_number' => 'required|string|min:8|max:12',
                'profession_id' => 'required|numeric',
                'profile_image' => 'nullable|numeric',
                'resume_file' => 'nullable|numeric',
                'resources' => 'nullable|array',
                'resources.*' => 'numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $user = auth()->user();
            $profile = UserProfile::where('user_id', $user->id)->first();
            $data = array_merge($validator->validate(), ['user_id' => $user->id]);

            if ($profile && $profile->id) {
                $profile->update($data);
            } else {
                $profile = UserProfile::create($data);
            }

            $resources = Resource::find(request()->get('resources'));

            $profile->resources()->detach();
            $profile->resources()->attach($resources);

            return response()->json(['message' => 'Profile updated.']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

}
