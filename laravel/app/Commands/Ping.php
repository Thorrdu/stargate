<?php

namespace App\Commands;

class Ping extends CommandHandler implements CommandInterface
{
    public $maxTime;
    public $paginatorMessage;
    public $listner;

    public function execute()
    {
        echo PHP_EOL.'Execute Ping';
        $this->message->channel->sendMessage('Ping...')->then(function ($messageSent){
            $latency = $messageSent->timestamp - $this->message->timestamp;
            $messageSent->channel->editMessage($messageSent->id, 'Pong! Latency: '.$latency.'ms');
        });
    }
}
