<?php

namespace App\Controllers\V1;

use App\Controllers\BaseApiController;
use App\Enums\DB\SQL;
use App\Enums\Http\Status;
use App\Models\Folder;
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

    protected function getModelClass(): string
    {
        return Folder::class;
    }
}
