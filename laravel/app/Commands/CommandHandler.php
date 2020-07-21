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

        $this->log();
    }

    public function log()
    {
        $log = new CommandLog;
        $log->player_id = $this->message->author->id;
        $log->command_type = $this->name;
        $log->command_raw = $this->message->content;
        $log->save();
    }

    public function help()
    {
        $helpMessage = $this->name."\n".$this->description."\n".$this->usage;
        return $helpMessage;
    }
}
