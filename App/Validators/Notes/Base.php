<?php

namespace App\Validators\Notes;

use App\Enums\DB\SQL;
use App\Models\Folder;
use App\Models\Note;
use App\Validators\BaseValidator;

class Base extends BaseValidator
{
    protected static array $skip = ['user_id', 'content', 'pinned', 'completed', 'created_at', 'updated_at'];

    protected static function isBoolean(array $fields, string $key): bool
    {
        if (empty($fields[$key])) {
            return true;
        }

        $result = is_bool($fields[$key]) || $fields[$key] === 1;

        if (!$result) {
            static::setError($key, "[$key] should be boolean");
        }

        return $result;
    }

    protected static function validateFolderId(int $folderId): bool
    {
        $folder = Folder::find($folderId);

        if ($folder) {
            return is_null($folder->user_id) || $folder->user_id === authId();
        }

        return false;
    }

    protected static function checkTitleOnDuplicate(string $title, int $folder_id): bool
    {
        $result = Note::where('user_id', SQL::EQUAL, authId())
            ->and('folder_id', SQL::EQUAL, $folder_id)
            ->and('title', SQL::EQUAL, $title)
            ->exists();

        if ($result) {
            static::setError('title', "The folder with name [$title] already exists!");
        }

        return $result;
    }
}