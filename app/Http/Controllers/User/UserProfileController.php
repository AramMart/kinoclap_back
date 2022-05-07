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

    public function single()
    {
        $id = auth()->user()->id;
        $user = User::with(['profile','profile.profileImage', 'profile.resumeFile', 'profile.resources'])->find($id);
        return response()->json($user);
    }

    public function update()
    {
       try {
           $validator = Validator::make(request()->all(), [
               'description' => 'string|min:3',
               'phone_number' => 'string|min:8|max:12',
               'profile_image' => 'nullable|numeric',
               'resume_file' => 'nullable|numeric',
               'resources' => 'nullable|array',
               'resources.*' => 'numeric',
           ]);

           if($validator->fails()){
               return response()->json(['message'=> $validator->errors()->first()],400);
           }

           $user = auth()->user();
           $profile = UserProfile::where('user_id',$user->id)->first();
           $data = array_merge($validator->validate(), ['user_id' => $user->id]);

           if ($profile && $profile->id) {
                $profile->update($data);
           } else {
               $profile = UserProfile::create($data);
           }

           $resources = Resource::find(request()->get('resources'));

           $profile->resources()->detach();
           $profile->resources()->attach($resources);

           return response()->json(['message'=> 'Profile updated.']);

       } catch (\Exception $e) {
           return response()->json(['message'=> $e->getMessage()],400);
       }
    }

}
