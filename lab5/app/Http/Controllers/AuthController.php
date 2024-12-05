<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller implements HasMiddleware
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login', 'forgotPassword', 'resetPassword']]); ↓↓↓
    }

    /**
     * https://laracoding.com/exclude-middleware-from-route-or-method/
     * In Laravel 11+ defines Middleware of a controller in a static middleware() function
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login', 'forgotPassword', 'resetPassword']),
        ];
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }

   /**
     * Get the token for a password reset request.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(
            ['email' => 'required|email']
        );
        $user = User::where('email', $request->only('email'))->firstOrFail();
        $token = Password::createToken($user);

        Log::channel('stderr')->info($token);

        return response()->json(null, 202);
    }

    /**
     * Reset a user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:3',
        ]);

        $status = Password::reset(
            $request->only('password', 'email', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)]);
                $user->save();
            }
        );

        $code = $status === Password::PASSWORD_RESET ? 200 : 400;
        return response()->json(['message' => [__($status)]], $code);
    }

}
