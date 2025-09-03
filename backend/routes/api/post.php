<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;

// Group Controller
Route::controller(PostController::class)->group(function(){
    Route::get('/', 'index');
    Route::post('/create', 'create');
});

// Subdmain routing
// Dù ở trong prefix thì domain vẫn tác động http://staging.localhost:8080/api/posts
Route::domain('staging.localhost')->group(function(){
    Route::get('/', [PostController::class, 'index']);
    Route::post('/create', [PostController::class, 'create']);
});

