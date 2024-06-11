<?php

namespace App\Models;

use Core\Model;

class User extends Model{
    protected static ?string $tableName = 'users';
    public int $id;
    public string $name;
    public ?string $email, $age;
}
