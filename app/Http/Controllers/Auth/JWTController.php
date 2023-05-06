<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\MailSender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JWTController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['login', 'register', 'verifyAccount', 'forgotPassword', 'resetPassword', 'test']]);
    }

    /**
     * Register user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:2|max:100',
            'last_name' => 'required|string|min:2|max:100',
            'middle_name' => 'nullable|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $email_verification_token = Str::random(64);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middle_name' => $request->middle_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verification_token' => $email_verification_token
        ]);

        // send email with the template
        MailSender::sendRegistrationVerificationMail($user, $email_verification_token);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyAccount()
    {
        $user = User::where('email_verification_token', request()->get('token'))->first();
        if(!is_null($user)) {
            $user->email_verification_token = null;
            $user->is_email_verified = true;
            $user->save();
            return response()->json(['message' => "Your e-mail is verified. You can now login."]);
        }
        return response()->json(['message' => 'Sorry your email cannot be identified.'], 422);
    }

    public function resetPassword()
    {
        $user = User::where('email_verification_token', request()->get('token'))->first();
        if(!is_null($user)) {
            $validator = Validator::make(request()->all(), [
                'password' => 'required|string|confirmed|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user->password = Hash::make(request()->get('password'));
            $user->email_verification_token = null;
            $user->save();

            return response()->json(['message' => "Password changed successfully"]);
        }
        return response()->json(['message' => 'Sorry cannot be identify user'], 422);
    }

    /**
     * login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'Invalid email or password'], 422);
        }

        $user = User::where('email', $request->get('email'))->first();

        if (!$user->is_email_verified) {
            return response()->json(['message' => 'Verify your email'], 422);
        }

        return $this->respondWithToken($token);
    }

    public function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->get('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid email'], 422);
        }

        if (!$user->is_email_verified) {
            return response()->json(['message' => 'First verify your email'], 422);
        }

        $email_verification_token = Str::random(64);
        $user->email_verification_token = $email_verification_token;
        $user->save();

        Mail::send(
            'auth.forgot-password',
            [
             'welcome_to' => env('MAIL_FROM_NAME'),
             'url' => env('FRONT_URL').'/'.env('FRONT_CREATE_NEW_PASSWORD').'/'.$email_verification_token
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->first_name)
                    ->subject('Change Password');
            }
        );

        return response()->json(['message' => 'Check Your email']);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'accessToken' => $token,
            'refreshToken' => $token,
            'expiresIn' => auth()->factory()->getTTL() * 60,
            'user' =>  ['general' => auth()->user(), 'profile' => UserProfile::where('user_id',auth()->user()->id)->with(['profileImage'])->first()]
        ]);
    }
}
