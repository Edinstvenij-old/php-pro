<?php

use Core\Router;

Router::post('api/auth/register')
    ->controller(\App\Controllers\AuthController::class)
    ->action('register');
Router::post('api/auth')
    ->controller(\App\Controllers\AuthController::class)
    ->action('auth');

// CRUD -> CREATE READ UPDATE DELETE -> index show store update delete

Router::get('api/v1/folders')
    ->controller(\App\Controllers\V1\FoldersController::class)
    ->action('index');
Router::get('api/v1/folders/{id:\d+}')
    ->controller(\App\Controllers\V1\FoldersController::class)
    ->action('show');
Router::post('api/v1/folders/store')
    ->controller(\App\Controllers\V1\FoldersController::class)
    ->action('store');
Router::put('api/v1/folders/{id:\d+}/update')
    ->controller(\App\Controllers\V1\FoldersController::class)
    ->action('update');
Router::delete('api/v1/folders/{id:\d+}/delete')
    ->controller(\App\Controllers\V1\FoldersController::class)
    ->action('delete');