<?php

namespace App\Controllers\V1;

use App\Controllers\BaseApiController;
use App\Enums\DB\SQL;
use App\Enums\Http\Status;
use App\Models\Folder;

class FoldersController extends BaseApiController
{
    public function index()
    {
        $folders = Folder::select(['id', 'title'])
            ->where('id', SQL::IN, [1, 2])
            ->and('title', SQL::EQUAL, 'folder 2')
            ->get();

        return $this->response(Status::OK, $folders);
    }

    public function show(int $id)
    {
        return $this->response(Status::OK, ['method' => 'show', 'id' => $id]);
    }

    public function store()
    {
        return $this->response(Status::OK, ['method' => 'store']);
    }

    public function update(int $id)
    {
        return $this->response(Status::OK, ['method' => 'update', 'id' => $id]);
    }

    protected function delete(int $id)
    {
        return $this->response(Status::OK, ['method' => 'delete', 'id' => $id]);
    }
}