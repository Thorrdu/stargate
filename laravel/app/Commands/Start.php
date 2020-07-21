<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;

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
        $this->message = null; //Factory message?
        $this->args = $args;
    }

    //BASIC CALL
    public function __construct2(Message $message,array $args) {
        $this->message = $message;
        $this->args = $args;
    }

    public function execute()
    {
        echo PHP_EOL.'Execute';
        $newPlayer = new Player;
        echo PHP_EOL.'After Declaraction';
        $newPlayer->user_id = 125641223544373248;
        $newPlayer->user_name = 'Thorrdu';
        $newPlayer->ban = false;
        $newPlayer->votes = 0;
        $newPlayer->last_claim = date("Y-m-d H:i:s");
        echo PHP_EOL.'Before Save';
        $newPlayer->save();   
        echo PHP_EOL.'Before Colony';     
        $newPlayer->addColony();
        echo PHP_EOL.'After Colony';
        //$this->message->channel->sendMessage('test executed');
        return '[Blabla Synopsis]\n\nPour afficher votre profile utilisez `!p`';
    }
}
