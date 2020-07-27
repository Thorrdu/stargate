<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use \Discord\Parts\Channel\Message as Message;
use App\CommandLog as CommandLog;
use App\Player;
use Discord\DiscordCommandClient as Discord; 

class CommandHandler
{
    public $name;
    public $message;
    public $args;
    public $player;
    public $discord;

    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct'.$numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    /*
    //CLI VERSION
    public function __construct1(array $args) {
        $this->message = null; //Factory message?
        $this->args = $args;
    }*/

    //BASIC CALL
    public function __construct2(Message $message,array $args, $discord){
        $this->message = $message;
        $this->args = $args;
        $this->player = Player::where('user_id', $message->author->id)->first();
        
        if(is_null($this->player) && !in_array(get_class($this),array('App\Commands\Start','App\Commands\Help')))
            return "Pour commencer votre aventure, utilisez `!start`";
        if(!is_null($this->player) && $this->player->ban)
            return "Vous êtes banni...";

        if(is_null($message->nonce))
        {
            $this->player->ban = true;
        }

        $this->log();
    }

    //BASIC CALL
    public function __construct3(Message $message, array $args, Discord $discord){
        $this->message = $message;
        $this->args = $args;
        $this->player = Player::where('user_id', $message->author->id)->first();
        $this->discord = $discord;

        if(is_null($message->nonce))
        {
            $this->player->ban = true;
        }

        $this->log();
    }


    public function log()
    {
        try{
            if(!is_null($this->player))
            {
                $log = new CommandLog;
                $log->player_id = $this->player->id;
                $log->command_type = str_replace("App\Commands\\",'',get_class($this));
                $log->command_raw = $this->message->content;
                $log->save();
            }
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function help()
    {
        $helpMessage = $this->name."\n".$this->description."\n".$this->usage;
        return $helpMessage;
    }
}
