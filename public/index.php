<?php

use App\Enums\Http\Status;
use Core\Router;
use Dotenv\Dotenv;

define('BASE_DIR', dirname(__DIR__));

require_once BASE_DIR . '/vendor/autoload.php';

try {
    $dotenv = Dotenv::createUnsafeImmutable(BASE_DIR);
    $dotenv->load();

    die(Router::dispatch($_SERVER['REQUEST_URI']));
} catch (PDOException $exception) {
    die(
    jsonResponse(
        Status::UNPROCESSABLE_ENTITY,
        [
            'errors' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]
        ]
    )
    );
} catch (Throwable $exception) {
    dd($exception);
    die(
    jsonResponse(
        Status::from($exception->getCode()),
        [
            'errors' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace()
            ]
        ]
    )
    );
}