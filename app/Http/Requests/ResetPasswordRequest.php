<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ResetPasswordRequest
{
    public static function validate(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'email' => 'required|email',
            'password' => ['required_with:password_confirmation|same:password_confirmation|min:6', new MatchOldPassword($user->password)],
            'password_confirmation' => 'required_with:password|same:password|min:6'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
