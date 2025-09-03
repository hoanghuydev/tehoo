<?php

use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Basic Route
Route::get('/', [UserController::class, 'index']);

Route::get('/test', function () {
    return response()->json(['message' => 'Hello World 1']);
})->name('test');


Route::post('/create', [UserController::class, 'create'])->name('create');

Route::match(['get', 'post'], '/{user}', [UserController::class, 'show'])->whereNumber('user');

Route::redirect("/userss", "/users"); // Status code is 302
Route::permanentRedirect("/usersss", "/users"); // Status code is 301

Route::view('/test-view', 'welcome');


// Route parameter
Route::get('/test-uri/{name?}', function ($name = 'default') {
    return response()->json(['message' => 'Hello World', 'name' => $name]);
})->where('name', '[a-z]+');


// Route Group Middleware
Route::middleware('test')->group(function(){
    Route::get('/category/{category}', function ($category) {
        return response()->json(['message' => 'Hello World', 'category' => $category]);
    })->whereIn('category', ['tech', 'science', 'health'])->name('category.index');
    
    Route::get('/search/{search}', function ($search) {
        return response()->json(['message' => 'Hello World', 'search' => $search]);
    })->where('search', '.*')->name('search');
});


// Named Route
Route::get('/test-name',function(){
    $test_url = route("users.test");
    return response()->json(['message' => 'Hello World', 'test_url' => $test_url]);
});

Route::get('/test-name-2',function(){
    return to_route("users.search",["search"=>"test-name","param"=>"hehe"]);
});


// Explicit Binding
Route::get('/user/{userId}', function(User $user){
    return response()->json(['message' => 'User', 'user' => $user]);
});
