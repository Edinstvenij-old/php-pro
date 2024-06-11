<?php


namespace App\Controllers\V1;

use App\Controllers\BaseApiController;
use App\Enums\DB\SQL;
use App\Enums\Http\Status;
use App\Models\User;

class UserController extends BaseApiController
{
    public function index()
    {
        $users = User::select(['name', 'age', 'email'])
            ->where('id', SQL::IN, [1, 5])
            //->and('name', SQL::EQUAL, 'Denys')
            ->get();

        return $this->response(Status::OK, $users);
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