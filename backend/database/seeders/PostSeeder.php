<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Post;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Post::create([
            'title' => 'Post 1',
            'content' => 'Content 1',
            'slug' => 'post-1',
            'user_id' => 1,
            'is_active' => true,
            'category' => 'tech',
        ]);
        Post::create([
            'title' => 'Post 2',
            'content' => 'Content 2',
            'slug' => 'post-2',
            'user_id' => 1,
            'is_active' => true,
            'category' => 'science',
        ]);
    }
}
