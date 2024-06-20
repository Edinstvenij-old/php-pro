<?php

namespace App\Controllers\V1;

use App\Controllers\BaseApiController;
use App\Enums\DB\SQL;
use App\Enums\Http\Status;
use App\Models\Folder;
use App\Models\Note;
use App\Validators\FolderValidator;
use Exception;

class FoldersController extends BaseApiController
{
    public function index(): array
    {
        $folders = Folder::where('user_id', SQL::EQUAL, authId())
            ->or('user_id', SQL::IS)
            ->orderBy([
                'user_id' => 'ASC',
                'title' => 'ASC'
            ])
            ->get();

        return $this->response(Status::OK, $folders);
    }

    public function show(int $id): array
    {
        return $this->response(Status::OK, Folder::find($id)->toArray());
    }

    public function store(): array
    {
        $fields = requestBody();

        if (FolderValidator::validate($fields) && $folder = Folder::create([...$fields, 'user_id' => authId()])) {
            return $this->response(Status::OK, $folder->toArray());
        }

        return $this->response(Status::OK, errors: FolderValidator::getErrors());
    }

    public function update(int $id): array
    {
        $fields = requestBody();
        $updateFields = [
            ...$fields,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (FolderValidator::validate($fields) && $folder = $this->model->update($updateFields)) {
            return $this->response(Status::OK, $folder->toArray());
        }

        return $this->response(Status::OK, errors: FolderValidator::getErrors());
    }

    public function delete(int $id): array
    {
        $result = Folder::destroy($id);

        if (!$result) {
            return $this->response(Status::UNPROCESSABLE_ENTITY, errors: [
                'message' => 'Oops, smth went wrong'
            ]);
        }

        return $this->response(Status::OK, $this->model->toArray());
    }

    public function notes(int $id)
    {
        $folder = Folder::find($id);

        if (!is_null($folder->user_id) && $folder->user_id !== authId()) {
            throw new Exception("This resource is forbidden for you", Status::FORBIDDEN->value);
        }

        $notes = match(true) {
            $folder->title === Folder::GENERAL_FOLDER && is_null($folder->user_id) => Note::where('user_id', SQL::EQUAL, authId())
                ->and('folder_id', SQL::EQUAL, $folder->id)
                ->get(),
            $folder->title === Folder::SHARED_FOLDER && is_null($folder->user_id) =>
            Note::select(['notes.*'])
                ->join(
                    'shared_notes',
                    [
                        [
                            'left' => 'notes.id',
                            'operator' => SQL::EQUAL->value,
                            'right' => 'shared_notes.note_id'
                        ],
                        [
                            'left' => 'shared_notes.user_id',
                            'operator' => SQL::EQUAL->value,
                            'right' => authId()
                        ]
                    ],
                    'INNER'
                )->get(),
            default => Note::where('folder_id', SQL::EQUAL, $folder->id)->get(),
        };

        return $this->response(Status::OK, $notes);
    }

    protected function getModelClass(): string
    {
        return Folder::class;
    }
}