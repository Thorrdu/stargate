<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;

class Start extends CommandHandler implements CommandInterface
{
    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct'.$numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    //CLI VERSION
    public function __construct1(array $args) {


        $this->message =  config('stargate.commands.start.usage');
        $this->args =  config('stargate.commands.start.usage');
    }

    //BASIC CALL
    public function __construct2(\Discord\Parts\Channel\Message $message,array $args) {

        $this->message =  config('stargate.commands.start.usage');
        $this->args =  config('stargate.commands.start.usage');
    }

    public function execute()
    {
        $this->message->channel->sendMessage('test executed');
        return 'test part 2';

    }
}
