<?php

namespace App\Models;

use Core\Model;

class User extends Model
{
    protected static ?string $tableName = 'users';

    public string $email, $password;
    public ?string $token, $token_expired_at, $created_at, $updated_at;
}