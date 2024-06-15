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
    // Перегляд всіх папок
    public function index(): array
    {
        $folders = Folder::where('user_id', SQL::EQUAL, authId())
            ->orWhere('user_id', SQL::IS, null)
            ->orderBy('user_id', 'ASC')
            ->orderBy('title', 'ASC')
            ->get();

        return $this->response(Status::OK, $folders);
    }

    // Перегляд папки по id
    public function show(int $id): array
    {
        $folder = Folder::find($id);

        if (!$folder) {
            return $this->response(Status::NOT_FOUND, errors: [
                'message' => 'Folder not found'
            ]);
        }

        return $this->response(Status::OK, $folder->toArray());
    }

    // Створення нової папки
    public function store(): array
    {
        $fields = requestBody();

        if (FolderValidator::validate($fields)) {
            $fields['user_id'] = authId();
            $folder = Folder::create($fields);

            return $this->response(Status::CREATED, $folder->toArray());
        }

        return $this->response(Status::UNPROCESSABLE_ENTITY, errors: FolderValidator::getErrors());
    }

    // Оновлення існуючої папки
    public function update(int $id): array
    {
        $fields = requestBody();
        $folder = Folder::find($id);

        if (!$folder) {
            return $this->response(Status::NOT_FOUND, errors: [
                'message' => 'Folder not found'
            ]);
        }

        if (FolderValidator::validate($fields)) {
            $fields['updated_at'] = date('Y-m-d H:i:s');
            $folder->update($fields);

            return $this->response(Status::OK, $folder->toArray());
        }

        return $this->response(Status::UNPROCESSABLE_ENTITY, errors: FolderValidator::getErrors());
    }

    // Видалення папки
    public function destroy(int $id): array
    {
        $folder = Folder::find($id);

        if (!$folder) {
            return $this->response(Status::NOT_FOUND, errors: [
                'message' => 'Folder not found'
            ]);
        }

        $folder->delete();

        return $this->response(Status::NO_CONTENT);
    }

    protected function getModelClass(): string
    {
        return Folder::class;
    }
}
