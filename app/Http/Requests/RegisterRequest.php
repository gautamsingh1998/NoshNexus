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
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
