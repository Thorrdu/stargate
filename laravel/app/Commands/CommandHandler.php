<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use \Discord\Parts\Channel\Message as Message;
use App\CommandLog as CommandLog;

class CommandHandler
{
    public $name;
    public $message;
    public $args;
    public $discord;

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
        $this->message = null; //Factory message?
        $this->args = $args;
    }

    //BASIC CALL
    public function __construct2(Message $message,array $args) {
        $this->message = $message;
        $this->args = $args;
    }

    public function log()
    {
        $log = new CommandLog;
        $log->type = $this->name;
        $log->raw = $this->message->content;
        $log->save();
    }

    public function help()
    {
        $helpMessage = $this->name."\n".$this->description."\n".$this->usage;
        return $helpMessage;
    }
}
