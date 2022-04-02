<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class JWTController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.custom_auth', ['except' => ['login', 'register', 'verifyAccount']]);
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
            'middle_name' => 'string|min:2|max:100',
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
        Mail::send(
            'auth.welcome_email',
            ['first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'middle_name' => $user->middle_name,
                'welcome_to' => env('MAIL_FROM_NAME'),
                'verification_token' => $email_verification_token
            ],
            function ($message) use ($user) {
                $message->to($user->email, $user->first_name)
                    ->subject('Verify Email');
            }
       );

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * @param Request $request
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyAccount(Request $request, $token)
    {
        $user = User::where('email_verification_token', $token)->first();
        $message = 'Sorry your email cannot be identified.';
        if(!is_null($user)) {
            $user->email_verification_token = null;
            $user->is_email_verified = true;
            $user->save();
            $message = "Your e-mail is verified. You can now login.";
        }
        return response()->json(['message' => $message], 200);
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
            return response()->json(['message' => 'Verify your email'], 404);
        }

        return $this->respondWithToken($token);
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
            'expiresIn' => auth()->factory()->getTTL() * 60
        ]);
    }
}
