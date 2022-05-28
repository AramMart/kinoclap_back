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
        $profiles = new User();

        if ($professionId) {
            $profiles = $profiles->whereHas('profile', function ($q) use ($professionId) {
                if ($professionId) {
                    $q->where('profession_id', $professionId);
                }
            });
        }

        $profiles = $profiles->with(['profile', 'profile.country','profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']);

        return response()->json($profiles->get());
    }


    public function searchProfile()
    {
        $profiles = User::where('id', '<>', 1);
        $search = request()->search;

        if ($search) {
            $chunks = explode(" ", $search);

            $profiles = $profiles->where(function ($query) use ($chunks) {
                if (count($chunks) > 1) {
                    $query->where(
                        [['first_name', 'LIKE', "%{$chunks[0]}%"], ['last_name', 'LIKE', "%{$chunks[1]}%"]]
                    )->orWhere(
                        [['first_name', 'LIKE', "%{$chunks[1]}%"], ['last_name', 'LIKE', "%{$chunks[0]}%"]]
                    );
                } else {
                    $query->where('first_name', 'LIKE', "%{$chunks[0]}%")
                        ->orWhere('last_name', 'LIKE', "%{$chunks[0]}%");
                }
            });

            return response()->json($profiles->get());
        } else {
            return response()->json([]);
        }
    }

    public function single()
    {
        $userId = auth()->user()->id;
        $user = User::with(['profile', 'profile.country', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources'])->find($userId);
        return response()->json($user);
    }

    public function singleById($userId)
    {
        $user = User::with(['profile', 'profile.country', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources'])->find($userId);
        return response()->json($user);
    }

    public function update()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'first_name' => 'required|string|min:3',
                'last_name' => 'required|string|min:3',
                'middle_name' => 'nullable|string|min:3',
                'description' => 'required|string|min:3',
                'phone_number' => 'required|string|min:8|max:12',
                'phone_code' => 'required|numeric',
                'profession_id' => 'nullable|numeric',
                'is_casting' => 'boolean',
                'profile_image' => 'nullable|numeric',
                'resume_file' => 'nullable|numeric',
                'country_id' => 'required|numeric',
                'gender' => 'required|in:MALE,FEMALE',
                'age' => 'required|numeric',
                'resources' => 'nullable|array',
                'resources.*' => 'numeric',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $user = auth()->user();

            $data = $validator->validate();

            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];
            $user->middle_name = $data['middle_name'];
            $user->save();

            $profile = $user->profile;
            $data = array_merge($data, ['user_id' => $user->id]);

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
