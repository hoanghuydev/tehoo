<?php 
namespace App\Kafka\Handlers;

use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;

class PostNotifyHandler
{
    public function __invoke(ConsumerMessage $message)
    {
        Log::info('PostNotifyHandler: ' . json_encode($message->getBody()));
    }
}