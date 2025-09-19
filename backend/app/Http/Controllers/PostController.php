<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use App\Kafka\Producers\PostProducer;
use Illuminate\Support\Facades\App;

class PostController extends Controller
{
    //
    public function index()
    {
        return response()->json(['message' => 'Posts']);
    }
    public function create(Request $request)
    {
        $slug = Str::slug($request->title);
        $post = Post::create($request->only('title','content','category','is_active') + ['slug' => $slug]);
        App::make(PostProducer::class)->createPost($post);
        return response()->json(['message' => 'Create Post', 'data' => $request->all()]);
    }

    public function update(Request $request, $id)
    {
        $slug = Str::slug($request->title);
        Post::find($id)->update($request->only('title','content','category','is_active') + ['slug' => $slug]);
        App::make(PostProducer::class)->updatePost(Post::find($id));
        return response()->json(['message' => 'Update Post', 'data' => $request->all()]);
    }
}
