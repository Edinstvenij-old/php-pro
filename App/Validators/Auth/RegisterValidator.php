<?php

namespace App\Validators\Auth;

class RegisterValidator extends Base
{
    static protected array $errors = [
        'email' => 'Email is incorrect',
        'password' => 'Password is incorrect. Minimum length is 8 symbols'
    ];

    static public function validate(array $fields = []): bool
    {
        $result = [
            parent::validate($fields),
            !static::checkEmailOnExists($fields['email'])
        ];

        return !in_array(false, $result);
    }
}