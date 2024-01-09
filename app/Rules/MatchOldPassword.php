<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;
class MatchOldPassword implements Rule
{
    protected $hashedPasswordFromDB;

    public function __construct($hashedPasswordFromDB)
    {
        $this->hashedPasswordFromDB = $hashedPasswordFromDB;
    }

    public function passes($attribute, $value)
    {
        return !Hash::check($value, $this->hashedPasswordFromDB);
    }

    public function message()
    {
        return 'The current password and new password cannot be the same.';
    }
}


