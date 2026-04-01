<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorBookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

// public (auth)
Route::post('auth/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth')
    ->name('auth.register');

Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:auth')
    ->name('auth.login');


// public (reading)
Route::get('books', [BookController::class, 'index'])
    ->name('books.index');
Route::get('books/{book}', [BookController::class, 'show'])
    ->name('books.show');
Route::get('authors', [AuthorController::class, 'index'])
    ->name('authors.index');
Route::get('authors/{author}', [AuthorController::class, 'show'])
    ->name('authors.show');
Route::get('authors/{author}/books', [AuthorBookController::class, 'index'])
    ->name('authors.books.index');

// protected (auth & writing)
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout'])
        ->name('auth.logout');
    Route::post('auth/logout/all', [AuthController::class, 'logoutAll'])
        ->name('auth.logout-all');
    Route::get('auth/me', [AuthController::class, 'me'])
        ->name('auth.me');

    Route::post('books', [BookController::class, 'store'])
        ->name('books.store');
    Route::put('books/{book}', [BookController::class, 'update'])
        ->name('books.update');
    Route::delete('books/{book}', [BookController::class, 'destroy'])
        ->name('books.destroy');

    Route::post('authors', [AuthorController::class, 'store'])
        ->name('authors.store');
    Route::put('authors/{author}', [AuthorController::class, 'update'])
        ->name('authors.update');
    Route::delete('authors/{author}', [AuthorController::class, 'destroy'])
        ->name('authors.destroy');
});
