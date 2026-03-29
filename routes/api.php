<?php

use App\Http\Controllers\AuthorBookController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('books', BookController::class);
Route::apiResource('authors', AuthorController::class);

Route::get('authors/{author}/books', [AuthorBookController::class, 'index'])
     ->name('authors.books.index');
