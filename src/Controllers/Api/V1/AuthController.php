<?php

namespace CQN\NAuthModule\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use CQN\NAuthModule\Requests\Auth\LoginRequest;
use CQN\NAuthModule\Requests\Auth\RegisterRequest;
use Illuminate\Http\Response;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Nette\Utils\Random;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {

            return response()->json([
                'success' => false,
                'message' => 'Email already exists',
            ], 400);
        }

        $user = User::create($request->validated());

        if ($user) {
            $credentials = $request->only('email', 'password');
            if (Auth::attempt($credentials)) {
                $token = Auth::user()->createToken('AccessToken')->accessToken;

                return response()->json([
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user' => $user,
                    'token' => $token,
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to register user',
        ]);
    }
    public function login(LoginRequest $request)
    {

        if (Auth::attempt($request->validated())) {
            $token = Auth::user()->createToken('AccessToken')->accessToken;

            return response()->json([
                'success' => true,
                'data' => Auth::user(),
                'token' => $token,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorised',
            ]);
        }
    }

    public function googleLogin()
    {
        try {
            $url = Socialite::driver('google')->stateless()
                ->redirect()->getTargetUrl();
            return response()->json([
                'url' => $url,
            ])->setStatusCode(Response::HTTP_OK);
        } catch (\Exception $exception) {
            return $exception;
        }
    }
    public function googleLoginCallback(Request $request)
    {
        try {
            $state = $request->input('state');

            parse_str($state, $result);
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->email)->first();
            if ($user) {
                // Gọi lại account cũ
                Auth::login($user);
                $token = Auth::user()->createToken('AccessToken')->accessToken;
                return response()->json([
                    'success' => true,
                    'data' => $user,
                    'firstLogin' => false,
                    'token' => $token
                ], Response::HTTP_OK);
            }
            // Tạo account mới
            $user = User::create(
                [
                    'email' => $googleUser->email,
                    'full_name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'username' => 'user-gg-' . Random::generate(12),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Random::generate(15),
                ]
            );
            Auth::login($user);
            $token = Auth::user()->createToken('AccessToken')->accessToken;
            return response()->json([
                'success' => true,
                'data' => $user,
                'firstLogin' => true,
                'token' => $token
            ], Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    public function logout()
    {
        $user = Auth::user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }
}
