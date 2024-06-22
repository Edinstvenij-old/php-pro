<?php
namespace App\Controllers\V1;
use App\Controllers\BaseApiController;
use App\Enums\DB\SQL;
use App\Enums\Http\Status;
use App\Models\Note;
use App\Validators\Notes\CreateNoteValidator;
use Exception;
class NotesController extends BaseApiController
{
    public function index(): array
    {
        return $this->response(
            Status::OK,
            Note::where('user_id', SQL::EQUAL, authId())
                ->orderBy([
                    'pinned' => 'DESC',
                    'completed' => 'ASC',
                    'updated_at' => 'DESC'
                ])
                ->get());
    }
    public function show(int $id): array
    {
        $note = Note::find($id);
        if (!$note) {
            return $this->response(Status::NOT_FOUND, errors: ['message' => 'Note not found']);
        }
        if ($note->user_id !== authId()) {
            return $this->response(Status::FORBIDDEN, errors: ['message' => 'This resource is forbidden for you']);
        }
        return $this->response(Status::OK, Note::find($id)->toArray());
    }
    public function store(): array
    {
        $fields = requestBody();
        if (CreateNoteValidator::validate($fields) && $note = Note::create([...$fields, 'user_id' => authId()])) {
            return $this->response(Status::OK, $note->toArray());
        }
        return $this->response(Status::OK, errors: CreateNoteValidator::getErrors());
    }

    public function update(int $id): array
    {
                $fields = requestBody();
        $updateFields = [
            ...$fields,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (CreateNoteValidator::validate($fields) && $Note = $this->model->update($updateFields)) {
            return $this->response(Status::OK, $Note->toArray());
        }

        return $this->response(Status::OK, []);
    }

    public function delete(int $id): array
    {
        $result = Note::destroy($id);
        if (!$result) {
            return $this->response(Status::UNPROCESSABLE_ENTITY, errors: [
                'message' => 'Oops, smth went wrong'
            ]);
        }
        return $this->response(Status::OK, $this->model->toArray());
    }
    protected function getModelClass(): string
    {
        return Note::class;
    }
}