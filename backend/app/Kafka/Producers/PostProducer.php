<?php 
namespace App\Kafka\Producers;

use App\Models\Post;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class PostProducer
{
    public function createPost(Post $post)
    {
        $message = new Message(
            headers: [
                'event_type' => 'post_created'
            ],
            body: [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'post_content' => $post->content,
            ],
            key: 'post_create'
        );
        Kafka::publish('broker')
        ->onTopic('posts')
        ->withMessage($message)
        ->send();
    }

    public function updatePost(Post $post)
    {
        $message = new Message(
            headers: [
                'event_type' => 'post_updated'
            ],
            body: [
                'post_id' => $post->id,
                'post_title' => $post->title,
                'post_content' => $post->content,
            ],
            key: 'post_update'
        );
        Kafka::publish('broker')
        ->onTopic('posts')
        ->withMessage($message)
        ->send();
    }
}