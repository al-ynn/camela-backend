<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/storage/{path}', function (string $path) {
    $decodedPath = ltrim(rawurldecode($path), '/');

    if (! Storage::disk('public')->exists($decodedPath)) {
        abort(404);
    }

    return Storage::disk('public')->response($decodedPath);
})->where('path', '.*');
