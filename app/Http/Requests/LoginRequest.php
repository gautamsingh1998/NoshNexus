<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginRequest
{
    public static function validate(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
