<?php

use App\Enums\Http\Status;
use Core\DB;

function db(): PDO
{
    return DB::connect();
}

function jsonResponse(Status $status, array $data = []): string
{
    header_remove();
    http_response_code($status->value);
    header("Content-Type: application/json");
    header("Status: {$status->value} {$status->name}");

    return json_encode([
        'code' => $status->value,
        'status' => "{$status->value} {$status->name}",
        'data' => $data
    ]);
}

function requestBody(): array
{
    $data = [];

    $requestBody = file_get_contents("php://input");

    if (!empty($requestBody)) {
        $data = json_decode($requestBody, true);
    }

    return $data;
}
