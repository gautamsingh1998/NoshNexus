<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Jobs\SendForgetPasswordEmailJob;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','forgetPassword','resetPassword','verifyOtp','redirectToGoogle','handleGoogleCallback']]);
    }

    public function register(Request $request)
    {
        try {
            RegisterRequest::validate($request);
            
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
            return response()->json(['success' => true, 'msg' => 'User registered successfully'], 200);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 422);

        } catch (Exception $e) {
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
            LoginRequest::validate($request);

            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Email and password do not match.'], 401);
            }

            return $this->respondWithToken($token);

        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->validator->errors()], 422);

        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong', 'msg' => $e->getMessage()], 500);
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();

            $token = auth()->login($user);

            return $this->respondWithToken($token);
        } catch (Exception $e) {
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
            'user' => auth()->user()
            //'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Forget Password 
    */
    public function forgetPassword(Request $request)
    {
        try {
            ForgetPasswordRequest::validate($request);

            $email = $request->input('email');
            $user = User::where('email', $email)->first();

            if ($user) {
                $otp = rand(1000, 9999);
                $otp_updated = Carbon::now();
                dispatch(new SendForgetPasswordEmailJob($user, $otp));
                User::where('email', $email)->update(['otp' => $otp, 'otp_update_at' => $otp_updated]);

                return response()->json(['success' => true, 'msg' => 'Email has been sent.'], 200);
            } else {
                return response()->json(['success' => false, 'msg' => "Email doesn't exist."], 401);
            }
        } catch (ValidationException $e) {
            // Handle validation error
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            // Handle other exceptions
            return response()->json(['success' => false, 'msg' => 'An unexpected error occurred.'], 500);
        }
    }
    
    /**
     * Reset Password
    */
    public function resetPassword(Request $request)
    {
        try {
            ResetPasswordRequest::validate($request);

            $email = $request->input('email');
            $password = $request->input('password');
            
            $user = User::where('email', $email)->first();

            if ($user) {
                // Reset the password
                $user->password = Hash::make($password);
                $user->save();

                return response(['success' => true, 'msg' => "Password updated successfully!"], 200);
            } else {
                return response(['success' => false, 'msg' => "User not found or email is incorrect."], 404);
            }
        } catch (ValidationException $e) {
            // Handle validation error
            return response(['success' => false, 'msg' => $e->getMessage()], 422);
        } catch (Exception $e) {
            // Handle other exceptions
            return response(['success' => false, 'msg' => 'An unexpected error occurred.'], 500);
        }
    }

    public function verifyOtp(Request $request){
        $user = User::where('otp_update_at',  '>=',  Carbon::now()->subMinutes(5)->toDateTimeString() )->where('otp', $request->otp)->first();
        if($user){
            return response(['success'=>true, "msg" => "OTP has been verified."], 200);
        }
        else{
            return response(['success'=>false, 'msg' => 'OTP is Invalid!'], 400);
        }
    }

}
