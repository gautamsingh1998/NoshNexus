<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class ResetPasswordRequest
{
    public static function validate(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Handle the case where the user is not found
            throw new ValidationException(['User' => 'User not found.']);
        }

        $hashedPasswordFromDB = $user->password;
        $validator = app('validator')->make($request->all(), [
            'email' => 'required|email',
            'password' => [
                'required_with:password_confirmation',
                'same:password_confirmation',
                'min:6',
                new MatchOldPassword($hashedPasswordFromDB)
            ],
            'password_confirmation' => 'required_with:password|same:password|min:6',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
