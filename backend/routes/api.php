<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('users')->name('users.')->group(base_path('routes/api/user.php'));

Route::prefix('posts')->name('posts.')->group(base_path('routes/api/post.php'));