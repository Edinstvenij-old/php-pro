<?php

namespace App\Models;

use Core\Model;

class Note extends Model
{
    protected static ?string $tableName = 'notes';

    public int $user_id, $folder_id;
    public ?string $created_at, $updated_at, $title, $content;
    public bool $pinned, $completed;
}