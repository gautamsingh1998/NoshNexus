<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        try {
                // Validate the incoming request data
                RegisterRequest::validate($request);
                // Create a new user
                $user = User::create([
                    'username' => $request->username,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

            if ($user) {
                # Attempt to log in the user and generate a token
              
                $credentials = $request->only(['email', 'password']);

                if (! $token = auth()->attempt($credentials)) {
                    return response()->json(['success' => false, 'msg' => 'Something went wrong. Please try again.'], 500);
                }
                # Respond with the generated tokens
                return $this->respondWithToken($token);
            } else {
                return response()->json(['success' => false, 'msg' => 'User creation failed.'], 500);
            }
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during registration
           ($e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Something went wrong with the request.'], 500);
        }
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
 

    public function login(Request $request)
    {
        try {
            // Validate the login request
            LoginRequest::validate($request);

            // Attempt to authenticate the user
            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Email and password do not match.'], 401);
            }

            // If authentication is successful, respond with the token
            return $this->respondWithToken($token);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json(['error' => 'Validation failed', 'errors' => $e->validator->errors()], 422);

        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => 'Something went wrong', 'msg' => $e->getMessage()], 500);
        }
    }


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}