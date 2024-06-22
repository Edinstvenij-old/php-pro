<?php

namespace App\Controllers\V1;

use App\Controllers\BaseApiController;
use App\Enums\DB\SQL;
use App\Enums\Http\Status;
use App\Models\Note;
use App\Models\SharedNote;
use App\Validators\SharedNoteValidator;
use Exception;

class SharedNotesController extends BaseApiController
{
    protected ?Note $note;

    public function before(string $action, array $params = []): bool
    {
        $this->note = Note::find($params['noteId']);

        if (!$this->note) {
            throw new Exception("Resource is not found", Status::NOT_FOUND->value);
        }

        if ($this->note->user_id !== authId()) {
            throw new Exception("This resource is forbidden for you", Status::FORBIDDEN->value);
        }

        return true;
    }

    public function add(int $noteId)
    {
        $data = [
            ...requestBody(),
            'note_id' => $noteId
        ];

        if (
            SharedNoteValidator::validate($data) &&
            SharedNoteValidator::isNotSharedWithUser($data) &&
            SharedNote::create($data)
        ) {
            return $this->response(Status::OK, $this->note->toArray());
        }

        return $this->response(Status::UNPROCESSABLE_ENTITY, errors: SharedNoteValidator::getErrors());
    }

    public function remove(int $noteId)
    {
        $data = [
            ...requestBody(),
            'note_id' => $noteId
        ];

        if (SharedNoteValidator::validate($data) && !SharedNoteValidator::isNotSharedWithUser($data)) {
            $sharedNote = SharedNote::where('note_id', SQL::EQUAL, $noteId)
                ->and('user_id', SQL::EQUAL, $data['user_id'])
                ->first();

            if (!$sharedNote) {
                return $this->response(Status::NOT_FOUND, errors: ['message' => 'Shared note not found']);
            }

            SharedNote::destroy($sharedNote->id);

            return $this->response(Status::OK, $sharedNote->toArray());
        }

        return $this->response(Status::UNPROCESSABLE_ENTITY, errors: SharedNoteValidator::getErrors());
    }

    protected function getModelClass(): string
    {
        return SharedNote::class;
    }
}