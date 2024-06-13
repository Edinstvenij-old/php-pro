<?php

namespace App\Validators\Auth;

class AuthValidator extends Base
{
    const DEFAULT_MESSAGE = 'Email or password is incorrect';

    static protected array $errors = [
        'email' => self::DEFAULT_MESSAGE,
        'password' => self::DEFAULT_MESSAGE
    ];

    static public function validate(array $fields = []): bool
    {
        $result = [
            parent::validate($fields),
            static::checkEmailOnExists($fields['email'], false, self::DEFAULT_MESSAGE)
        ];

        return !in_array(false, $result);
    }
}