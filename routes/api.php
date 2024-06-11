<?php

use Core\Router;

Router::post('api/auth/register')
    ->controller(\App\Controllers\AuthController::class)
    ->action('register');
Router::post('api/auth')
    ->controller(\App\Controllers\AuthController::class)
    ->action('auth');

// CRUD -> CREATE READ UPDATE DELETE -> index show store update delete

Router::get('api/v1/users')
    ->controller(\App\Controllers\V1\UserController::class)
    ->action('index');
Router::get('api/v1/users/{id:\d+}')
    ->controller(\App\Controllers\V1\UserController::class)
    ->action('show');
Router::post('api/v1/users/store')
    ->controller(\App\Controllers\V1\UserController::class)
    ->action('store');
Router::put('api/v1/users/{id:\d+}/update')
    ->controller(\App\Controllers\V1\UserController::class)
    ->action('update');
Router::delete('api/v1/users/{id:\d+}/delete')
    ->controller(\App\Controllers\V1\UserController::class)
    ->action('delete');