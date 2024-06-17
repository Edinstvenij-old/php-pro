<?php

namespace App\Controllers;

use App\Enums\Http\Status;
use App\Models\User;
use App\Validators\Auth\AuthValidator;
use App\Validators\Auth\RegisterValidator;
use Core\Controller;
use App\Models\TokenManager;
use ReallySimpleJWT\Token;

class AuthController extends Controller
{
    public function register()
    {
        $fields = requestBody();
        if (RegisterValidator::validate($fields)) {
            $user = User::create([
                'email' => $fields['email'],
                'password' => password_hash($fields['password'], PASSWORD_BCRYPT)
            ]);

            return $this->response(Status::OK, $user->toArray());
        }

        return $this->response(Status::UNPROCESSABLE_ENTITY, RegisterValidator::getErrors());
    }

    public function auth()
    {
        $fields = requestBody();

        if (AuthValidator::validate($fields)) {
            $user = User::findBy('email', $fields['email']);

            if ($user && password_verify($fields['password'], $user->password)) {
                $expiration = time() + 3600;
                $token = Token::create($user->id, $user->password, $expiration, 'localhost');

                // Store token in the database
                TokenManager::storeToken($user->id, $token, date('Y-m-d H:i:s', $expiration));

                return $this->response(Status::OK, [
                    'token' => $token,
                    'user' => $user->toArray(),
                ]);
            }
        }

        return $this->response(Status::UNPROCESSABLE_ENTITY, AuthValidator::getErrors());
    }
}
