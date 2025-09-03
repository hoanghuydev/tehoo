<?php

use App\Enums\PostCategory;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

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

// Implicit Binding
Route::get('/{post}', function(Post $post){
    // Mặc định lấy theo id. Cấu hình model getRouteKeyName() để lấy theo slug
    return response()->json(['message' => 'Find post by id', 'post' => $post]);
})->withTrashed(); // withTrashed() để lấy được post đã bị xóa

Route::get('/{post:slug}', function(Post $post){
    return response()->json(['message' => 'Find post by slug', 'post' => $post]);
})->missing(function(Request $request){
    return response()->json(['message' => 'Post not found'], 404);
});

Route::get('/user/{user}/post/{post}', function(User $user, Post $post){
    return response()->json(['message' => 'Find post by user and post', 'user' => $user, 'post' => $post]);
})->withoutScopedBindings(); // Cái này sẽ giống với mặc định không gán binding

// Scope Binding
Route::get('/user/{user}/post/{post}', function(User $user, Post $post){
    return response()->json(['message' => 'Find post by user and post', 'user' => $user, 'post' => $post]);
})->scopeBindings();

// hoặc
Route::scopeBindings()->group(function(){
    Route::get('/user/{user}/post/{post}', function(User $user, Post $post){
        return response()->json(['message' => 'Find post by user and post', 'user' => $user, 'post' => $post]);
    });
});

// Implicit Binding with Enum
Route::get('/category/{category}', function(PostCategory $category){
    return response()->json(['message' => 'Find post by category', 'category' => $category]);
});
