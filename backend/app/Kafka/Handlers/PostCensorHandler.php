<?php 
namespace App\Kafka\Handlers;

use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;

class PostCensorHandler
{
    public function __invoke(ConsumerMessage $message)
    {
        Log::info('PostCensorHandler: ' . json_encode($message->getBody()));
    }
}