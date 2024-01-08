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
            'password' => 'min:6|required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
