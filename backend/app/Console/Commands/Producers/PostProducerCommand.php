<?php

namespace App\Console\Commands\Producers;

use Illuminate\Console\Command;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class PostProducerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:producer {topic : The topic to publish to} {event_type : The event type} {message : The message to publish} {--publish : Whether to publish the message}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // use command php artisan kafka:producer posts post_created '{"post_id": 1, "post_title": "Test Post", "post_content": "Test Content"}' --publish
    public function handle()
    {
        //
        $publish = $this->option('publish');
        $payload = json_decode($this->argument('message'), true);
        if ($publish) {
            Kafka::publish('broker')
            ->onTopic($this->argument('topic'))
            ->withMessage(new Message(
                headers: [
                    'event_type' => $this->argument('event_type')
                ],
                body: [
                    'post_id' => $payload['post_id'],
                    'post_title' => $payload['post_title'],
                    'post_content' => $payload['post_content']
                ]
            ))
            ->send();
        }
    }
}
