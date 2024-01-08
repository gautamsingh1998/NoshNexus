<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegisterRequest
{
    public static function validate(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
           'username' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:6'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
