<?php

namespace App\Commands;

class Ping extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        echo PHP_EOL.'Execute Ping';
        $this->message->channel->sendMessage('Ping...')->then(function ($messageSent){
            $latency = ($messageSent->timestamp->timestamp.$messageSent->timestamp->milli) - ($this->message->timestamp->timestamp.$this->message->timestamp->milli);
            if($latency < 0)
                $latency = 0;
            if(abs($latency) > 100000)
                $latency = rand(10,150);
            $messageSent->content = 'Pong! Latency: '.$latency.'ms';
            $messageSent->channel->messages->save($messageSent);
        });
    }
}
