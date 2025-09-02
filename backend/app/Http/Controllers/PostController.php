<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    //
    public function index()
    {
        return response()->json(['message' => 'Posts']);
    }
    public function create(Request $request)
    {
        return response()->json(['message' => 'Create Post', 'data' => $request->all()]);
    }
}
