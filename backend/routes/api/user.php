<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index']);

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World']);
});


Route::post('/create', [UserController::class, 'create']);

Route::match(['get', 'post'], '/{user}', [UserController::class, 'show'])->whereNumber('user');

Route::redirect("/userss", "/users"); // Status code is 302
Route::permanentRedirect("/usersss", "/users"); // Status code is 301

Route::view('/test-view', 'welcome');

Route::get('/test-uri/{name?}', function ($name = 'default') {
    return response()->json(['message' => 'Hello World', 'name' => $name]);
})->where('name', '[a-z]+');

Route::get('/category/{category}', function ($category) {
    return response()->json(['message' => 'Hello World', 'category' => $category]);
})->whereIn('category', ['tech', 'science', 'health']);

