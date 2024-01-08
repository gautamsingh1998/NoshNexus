<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Jobs\SendForgetPasswordEmailJob;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','forgetPassword']]);
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

    /**
     * Forget Password 
    */
    public function forgetPassword(Request $request) {
        //ForgetPasswordRequest::validate($request);
       $email = request()->only('email');
       $user = User::where('email', $email)->first();

        if( $user){
            $otp = rand(1000,9999);
            
            $data = array('otp'=> $otp);

            try {
                $otp_updated = Carbon::now();

                dispatch(new SendForgetPasswordEmailJob($user, $otp));
                //SendForgetPasswordEmailJob::dispatch($user, $otp)->tries(3);

                
                $user = User::where('email','=',$email)->update(['otp' => $otp, 'otp_update_at' => $otp_updated->toDateTimeString()]);
              
                return response()->json(['success'=>true,'msg'=>' Email has been sent.'], 200);
            } catch (Throwable $e) {
                return response()->json(['success'=>false, 'msg' => $e->getMessage() ], 401);
            }
        }else{
            return response()->json(['success'=>false, 'msg' => "Email doesn't exist."], 401);
        }
    }
    
    /**
     * Reset Password
    */
    public function resetPassword(Request $request)
    {

        // Check input is valid
      ResetPasswordRequest::validate($request);
        $email = request()->only('email');
        $user = User::where('email', $email)->first();

        if($user){
            // Reset the password
            $user->password = Hash::make($request->password);
            $user->save();
            return response(['success'=>true, 'msg' => "Password updated successfully!."], 200);
        }else{
            return response(['success'=>true,'msg' => "Otp doesn't match."], 400);
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
