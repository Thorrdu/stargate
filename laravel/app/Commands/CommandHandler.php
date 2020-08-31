<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use \Discord\Parts\Channel\Message as Message;
use App\CommandLog as CommandLog;
use App\Player;
use Discord\DiscordCommandClient as Discord; 
use Illuminate\Support\Str;

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

    
    //CLI VERSION
    public function __construct1(array $args) {
        $this->message = null; //Factory message?
        $this->args = $args;
        $this->player = Player::where('user_id', 125641223544373248)->first();
    }

    //BASIC CALL
    public function __construct2(Message $message,array $args){
        $this->message = $message;
        $this->args = $args;
        $this->player = Player::where('user_id', $message->author->id)->first();

        if(!is_null($this->message->author->username) && !is_null($this->player))
        {
            if($this->player->untagged_user_name != $this->message->author->user->username || $this->player->untagged_user_name == "not loaded")
            {
                echo PHP_EOL.'News name: '.$this->message->author->user_name;
                echo PHP_EOL.'DIFFERENT';
                $this->player->user_name = $this->player->untagged_user_name = $this->message->author->user_name;

                if(!is_null($this->player->alliance))
                    $this->player->user_name = '['.$this->player->alliance->tag.'] '.$this->player->untagged_user_name;

                $this->player->save();
            }
        }


        if(is_null($this->player) && !in_array(get_class($this),array('App\Commands\Start','App\Commands\Help')))
            return "Pour commencer votre aventure, utilisez `!start`";
        if(!is_null($this->player) && $this->player->ban)
            return "Vous êtes banni...";

            /*
        if(is_null($message->nonce))
        {
            $this->player->ban = true;
            $this->player->save();
        }*/

        $this->log();
    }

    //BASIC CALL
    public function __construct3(Message $message, array $args, Discord $discord){
        $this->message = $message;
        $this->args = $args;
        $this->player = Player::where('user_id', $message->author->id)->first();

        if(!is_null($this->message->author->user_name) && !is_null($this->player))
        {
            if($this->player->untagged_user_name != $this->message->author->user->username || $this->player->untagged_user_name == "not loaded")
            {
                echo PHP_EOL.'News name: '.$this->message->author->user_name;
                echo PHP_EOL.'DIFFERENT';
                $this->player->user_name = $this->player->untagged_user_name = $this->message->author->user_name;

                if(!is_null($this->player->alliance))
                    $this->player->user_name = '['.$this->player->alliance->tag.'] '.$this->player->untagged_user_name;

                $this->player->save();
            }
        }

        $this->discord = $discord;

        /*
        if(is_null($message->nonce))
        {
            $this->player->ban = true;
        }*/

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
                if($this->player->captcha)
                    $log->captcha_flag = true;
                
                if(is_null($this->message->nonce) && $this->discord)
                {
                    $log->command_flag = true;
                    $this->player->captcha = true;
                    $this->player->captcha_key = Str::random(10);
                    $this->player->save();
                    $userExist = $this->discord->users->filter(function ($value){
                        return $value->id == $this->player->user_id;
                    });
                    if($userExist->count() > 0)
                    {
                        $foundUser = $userExist->first();
                        $foundUser->sendMessage(trans('generic.captchaLink', ['link' => 'https://web.thorr.ovh/captcha/'.$this->player->captcha_key], $this->player->lang));
                    }
                }
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
