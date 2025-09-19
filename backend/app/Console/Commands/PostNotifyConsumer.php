<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Junges\Kafka\Config\RebalanceStrategy;
use Junges\Kafka\Contracts\ConsumerMessage;
use Junges\Kafka\Facades\Kafka;

class PostNotifyConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kafka:consumer {group}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $group = $this->argument('group');
        $topics = config("kafka.consumer_groups.$group.topics");
        $handlerClass = config("kafka.consumer_groups.$group.handler");
        
        if (empty($topics) || !$handlerClass || !class_exists($handlerClass))
        {
            $this->error("Topics or handler not found for group: $group");
            return Command::FAILURE;
        }

        $this->info("Starting Kafka consumer group [$group] listening on topics: " . implode(',', $topics));

        $consumer = Kafka::consumer($topics)
        ->withHandler(function(ConsumerMessage $message) use ($handlerClass, $group) {
            $eventType = $message->getHeaders()['event_type'] ?? null;
            if ($eventType == config("kafka.consumer_groups.$group.event_type")) {
                $handler = new $handlerClass();
                $handler($message);
                $this->info("Event type [$eventType] handled for this consumer group: $group");
            } else {
                $this->error("Event type [$eventType] not found for this consumer group: $group");
            }
        })
        ->withRebalanceStrategy(RebalanceStrategy::RANGE)
        ->withBrokers(config('kafka.brokers'))
        ->withAutoCommit(false)
        ->build()
        ->consume();

        $this->info("Consumer started");

        return Command::SUCCESS;
    }
}
