<?php

namespace App\Validators\Notes;

class UpdateNoteValidator extends Base
{
    protected static array $rules = [
        'title' => '/[\w\s\(\)\-]{3,}/i',
        'folder_id' => '/\d+/i'
    ];

    protected static array $messages = [
        'title.regex' => 'Title should contain only characters, numbers, spaces, underscores, hyphens, and parentheses, and has a length of at least 3 characters',
        'folder_id.integer' => 'Folder ID should be an integer'
    ];

    public static function validate(array $fields = []): bool
    {
        $result = [
            parent::validate($fields),
            static::validateFolderId($fields['folder_id']),
            static::isBoolean($fields, 'pinned'),
            static::isBoolean($fields, 'completed')
        ];

        return !in_array(false, $result);
    }

    protected static function isBoolean(array $fields, string $fieldName): bool
    {
        return isset($fields[$fieldName]) ? is_bool($fields[$fieldName]) : true;
    }
}
