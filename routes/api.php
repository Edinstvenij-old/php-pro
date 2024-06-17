<?php

use Core\Router;
use App\Controllers\AuthController;
use App\Controllers\V1\FoldersController;
use App\Controllers\V1\NotesController;

// Authentication routes
Router::post('api/auth/register')
    ->controller(AuthController::class)
    ->action('register');
Router::post('api/auth')
    ->controller(AuthController::class)
    ->action('auth');

// CRUD routes for folders
Router::get('api/v1/folders')
    ->controller(FoldersController::class)
    ->action('index');
Router::get('api/v1/folders/{id:\d+}')
    ->controller(FoldersController::class)
    ->action('show');
Router::post('api/v1/folders/store')
    ->controller(FoldersController::class)
    ->action('store');
Router::put('api/v1/folders/{id:\d+}/update')
    ->controller(FoldersController::class)
    ->action('update');
Router::delete('api/v1/folders/{id:\d+}/delete')
    ->controller(FoldersController::class)
    ->action('delete');

// CRUD routes for notes
Router::get('api/v1/notes')
    ->controller(NotesController::class)
    ->action('index');
Router::get('api/v1/notes/{id:\d+}')
    ->controller(NotesController::class)
    ->action('show');
Router::post('api/v1/notes/store')
    ->controller(NotesController::class)
    ->action('store');
Router::put('api/v1/notes/{id:\d+}/update')
    ->controller(NotesController::class)
    ->action('update');
Router::delete('api/v1/notes/{id:\d+}/delete')
    ->controller(NotesController::class)
    ->action('delete');
