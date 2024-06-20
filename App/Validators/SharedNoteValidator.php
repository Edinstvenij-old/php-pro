<?php

namespace App\Validators;

use App\Enums\DB\SQL;
use App\Models\Note;
use App\Models\SharedNote;
use App\Models\User;

class SharedNoteValidator extends BaseValidator
{
    protected static array $rules = [
        'user_id' => '/\d+/i',
        'note_id' => '/\d+/i',
    ];
    protected static array $errors = [
        'user_id' => 'User id should be integer',
        'note_id' => 'Note id should be integer',
    ];

    protected static function isUserExists(int $userId): bool
    {
        $exists = User::where('id', SQL::EQUAL, $userId)->exists();

        if (!$exists) {
            static::setError('user_id', "User with id $userId does not exists!");
        }

        return $exists;
    }

    protected static function sharedUserIsNotOwner(int $userId, int $noteId): bool
    {
        $note = Note::find($noteId);

        if (!$note) {
            static::setError('note_id', "Note with id $userId does not exists!");
            return false;
        }

        return $note->user_id !== $userId;
    }

    public static function isNotSharedWithUser(array $fields): bool
    {
        $alreadyShared = SharedNote::where('user_id', SQL::EQUAL, $fields['user_id'])
            ->and('note_id', SQL::EQUAL, $fields['note_id'])
            ->exists();

        if ($alreadyShared) {
            static::setError(
                'message',
                "Note [id=$fields[note_id]] already shared with user[id=$fields[user_id]]"
            );
        }

        return !$alreadyShared;
    }

    public static function validate(array $fields = []): bool
    {
        $result = [
            parent::validate($fields),
            static::isUserExists($fields['user_id']),
            static::sharedUserIsNotOwner($fields['user_id'], $fields['note_id'])
        ];

        return !in_array(false, $result);
    }
}