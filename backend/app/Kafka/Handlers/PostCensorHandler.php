<?php 
namespace App\Kafka\Handlers;

use App\Models\Post;
use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;

class PostCensorHandler
{
    public function __invoke(ConsumerMessage $message)
    {
        $data = $message->getBody();
        if ($this->isCensored($data['post_content']) || $this->isCensored($data['post_title'])) {
            $postId = $data['post_id'];
            Post::find($postId)->update(['is_active' => false]);
        }
        Log::info('PostCensorHandler: ' . json_encode($message->getBody()));
    }
    public function isCensored($data)
    {
        return str_contains($data, 'censored');
    }
}