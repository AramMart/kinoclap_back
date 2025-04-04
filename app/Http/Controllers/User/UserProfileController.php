<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\News;
use App\Models\Resource;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\MailSender;
use Google\Service\Analytics\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => [ 'index', 'singleById']]);
        $this->middleware('check.payment');
    }

    public function index()
    {
        $professionId = request()->get('profession_id');
        $profiles = User::where('type', 'user')->whereHas('profile', function ($q) {
            $q->where('is_casting', false)->where('approved', 'ACCEPTED');
        });

        if (!auth()->user()) {
            //TODO hide phone
//            $profiles->withHidden(['phone_number']);
        } else {
            $profiles->where('id', '<>', auth()->user()->id);
        }

        if ($professionId) {
            $profiles = $profiles->whereHas('profile', function ($q) use ($professionId) {
                if ($professionId) {
                    $q->where('profession_id', $professionId);
                }

                $countryId = request()->get('country_id');
                $age = request()->get('age');
                $gender = request()->get('gender');
                if ($countryId) {
                    $q->where('country_id', $countryId);
                }

                if ($gender) {
                    $q->where('gender', $gender);
                }

                if ($age) {
                    $q->where('age', '<=', $age);
                }
            });
        }

        $profiles = $profiles->with(['profile', 'profile.country','profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']);

        return response()->json($profiles->paginate(100));
    }

    public function castings() {

        $profiles = User::whereHas('profile', function ($q)  {

            $countryId = request()->get('country_id');
            $age = request()->get('age');
            $gender = request()->get('gender');

            $q->where('is_casting', true)->where('approved', 'ACCEPTED');

            if ($countryId) {
                $q->where('country_id', $countryId);
            }

            if ($gender) {
                $q->where('gender', $gender);
            }

            if ($age) {
                $q->where('age', '<=', $age);
            }
        })->with(['profile', 'profile.country','profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']);

        if (auth()->user()) {
            $profiles->where('id', '<>', auth()->user()->id);
        }

        return response()->json($profiles->paginate(100));
    }

    public function searchProfile()
    {
        $profiles = User::where('id', '<>', 1)->whereHas('profile', function ($q) {
            $q->where('approved', 'ACCEPTED');
        })->with(['profile', 'profile.profileImage']);

        $search = request()->search;

        if (auth()->user()) {
            $profiles->where('id', '<>', auth()->user()->id);
        }

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
        $user = User::with(
            ['profile', 'profile.country', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']
        )->find($userId);
        return response()->json($user);
    }

    public function singleById($userId)
    {
        $user = User::with(
            ['profile', 'profile.country', 'profile.profileImage', 'profile.profession', 'profile.resumeFile', 'profile.resources']
        )->whereHas('profile', function ($q) {
            $q->where('approved', 'ACCEPTED');
        })->find($userId);

        //TODO hide phone
//        if (!auth()->user()) {
//            $user->makeHidden(['phone_number']);
//        }

        if (!$user) {
            return response()->json([],404);
        }
        return response()->json($user);
    }

    public function updateSettings()
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
                'resume_file' => 'nullable|numeric',
                'country_id' => 'required|numeric',
                'gender' => 'required|in:MALE,FEMALE',
                'age' => 'required|numeric',
                'facebook' => 'nullable|string',
                'instagram' => 'nullable|string',
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
            $data = array_merge($data, ['user_id' => $user->id,'approved' => 'PENDING']);

            if ($profile && $profile->id) {
                $profile->update($data);
            } else {
                UserProfile::create($data);
            }

            MailSender::sendNotificationForNewModeration('user');

            return response()->json(['message' => 'Profile updated.']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }


    public function updateProfileImage() {
            $validator = Validator::make(request()->all(), [
                'profile_image' => 'nullable|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $user = auth()->user();

            $data = $validator->validate();

            $profile = $user->profile;
            $data = array_merge($data, ['user_id' => $user->id, 'approved' => 'PENDING']);

            if ($profile && $profile->id) {
                $profile->update($data);
            } else {
                UserProfile::create($data);
            }

            MailSender::sendNotificationForNewModeration('user');

            return response()->json(['message' => 'Profile updated.']);
    }

    public function updateWorks()
        {
            try {
                $validator = Validator::make(request()->all(), [
                    'resources' => 'nullable|array',
                    'resources.*' => 'numeric',
                ]);

                if ($validator->fails()) {
                    return response()->json(['message' => $validator->errors()->first()], 400);
                }

                $user = auth()->user();

                $data = $validator->validate();

                $profile = $user->profile;
                $data = array_merge($data, ['user_id' => $user->id, 'approved' => 'PENDING']);

                if ($profile && $profile->id) {
                    $profile->update($data);
                } else {
                    $profile = UserProfile::create($data);
                }

                $resources = Resource::find(request()->get('resources'));

                $profile->resources()->detach();
                $profile->resources()->attach($resources);

                MailSender::sendNotificationForNewModeration('user');

                return response()->json(['message' => 'Profile updated.']);

            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 400);
            }
        }

}
