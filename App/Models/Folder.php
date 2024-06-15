<?php

namespace App\Models;

use Core\Model;

class Folder extends Model
{
    protected static ?string $tableName = 'folders';

    public ?int $user_id;
    public string $title;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    protected array $fillable = ['user_id', 'title', 'created_at', 'updated_at'];
}
