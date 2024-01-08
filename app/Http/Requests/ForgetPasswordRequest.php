<?php

namespace App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ForgetPasswordRequest
{
    public static function validate(Request $request)
    {
        $validator = app('validator')->make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
