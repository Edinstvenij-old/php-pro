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
        try {
            $folder = Folder::find($id);

            if (!$folder) {
                return $this->response(Status::NOT_FOUND, ['message' => 'Folder not found']);
            }

            return $this->response(Status::OK, $folder->toArray());
        } catch (Exception) {
            return $this->response(Status::INTERNAL_SERVER_ERROR, [
                'message' => 'Failed to fetch folder details'
            ]);
        }
    }

    public function store(): array
    {
        try {
            $fields = requestBody();

            if (FolderValidator::validate($fields)) {
                $folder = Folder::create([...$fields, 'user_id' => authId()]);
                return $this->response(Status::CREATED, $folder->toArray());
            }

            return $this->response(Status::UNPROCESSABLE_ENTITY, FolderValidator::getErrors());
        } catch (Exception) {
            return $this->response(Status::INTERNAL_SERVER_ERROR, [
                'message' => 'Failed to create folder'
            ]);
        }
    }

    public function update(int $id): array
    {
        try {
            $fields = requestBody();
            $updateFields = [
                ...$fields,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if (FolderValidator::validate($fields)) {
                $folder = Folder::find($id);

                if (!$folder) {
                    return $this->response(Status::NOT_FOUND, ['message' => 'Folder not found']);
                }

                $folder->update($updateFields);
                return $this->response(Status::OK, $folder->toArray());
            }

            return $this->response(Status::UNPROCESSABLE_ENTITY, FolderValidator::getErrors());
        } catch (Exception) {
            return $this->response(Status::INTERNAL_SERVER_ERROR, [
                'message' => 'Failed to update folder'
            ]);
        }
    }

    public function delete(int $id): array
    {
        try {

            $this->checkModelOwner('delete', [$id], Folder::class);

            $folder = Folder::find($id);
            if (!$folder) {
                return $this->response(Status::NOT_FOUND, [
                    'message' => 'Folder not found'
                ]);
            }

            $folder->delete();

            return $this->response(Status::OK, [
                'message' => 'Folder deleted successfully'
            ]);
        } catch (Exception) {
            return $this->response(Status::INTERNAL_SERVER_ERROR, [
                'message' => 'Failed to delete folder'
            ]);
        }
    }

    protected function getModelClass(): string
    {
        return Folder::class;
    }
}
