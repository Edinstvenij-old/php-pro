<?php

namespace App\Validators;

use App\Enums\DB\SQL;
use App\Models\Folder;

class FolderValidator extends BaseValidator
{
    protected static array $rules = [
        'title' => '/[\w\s\(\)\-]{3,}/i'
    ];

    protected static array $errors = [
        'title' => 'Title should contain only characters, numbers and _-() and has length more than 2 symbols'
    ];

    public static function validate(array $fields = []): bool
    {
        $result = [
            parent::validate($fields),
            !static::checkOnDuplicate($fields['title'])
        ];

        return !in_array(false, $result);
    }

    protected static function checkOnDuplicate(string $title): bool
    {   // select * from folders where (user_id = 5 OR user_id IS NULL) AND title = 'Test'
        $result = Folder::where('user_id', SQL::EQUAL, authId())
            ->and('title', SQL::EQUAL, $title)
            ->exists();

        if ($result) {
            static::setError('title', "The folder with name [$title] already exists!");
        }

        return $result;
    }
}