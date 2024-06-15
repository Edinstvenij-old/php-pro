<?php

use App\Enums\Http\Status;
use Core\DB;
use ReallySimpleJWT\Token;

function db(): PDO
{
    return DB::connect();
}

function jsonResponse(Status $status, array $data = []): string
{
    header_remove();
    http_response_code($status->value);
    header("Content-Type: application/json");
    header("Status: $status->value");

    return json_encode([
        ...$status->withDescription(),
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

function getAuthToken(): string
{
    $headers = apache_request_headers();

    if (empty($headers['Authorization'])) {
        throw new Exception('The request should contain an auth token', 422);
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);

    if (!Token::validateExpiration($token)) {
        throw new Exception('Token is invalid', 422);
    }

    return $token;
}

function authId(): int
{
    $token = Token::getPayload(getAuthToken());

    if (empty($token['user_id'])) {
        throw new Exception('Token structure is invalid', 422);
    }

    return $token['user_id'];
}