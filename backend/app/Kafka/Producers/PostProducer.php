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
        ->withConfigOption('acks', 'all') // Leader + Replicas Acknowledgment
        // 0 là at-most-once, 1 là at-least-once, all là at-least-once
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
        ->withConfigOption('acks', 'all') // Đảm bảo leader và tất cả replicas xác nhận
        ->withConfigOption('enable.idempotence', true) // Bật idempotence để đảm bảo exactly-once
        ->withConfigOption('max.in.flight.requests.per.connection', 5) // Giá trị khuyến nghị khi enable.idempotence
        ->withConfigOption('retries', 5) // Tăng số lần thử lại để đảm bảo gửi thành công
        ->withMessage($message)
        ->send();
    }
}