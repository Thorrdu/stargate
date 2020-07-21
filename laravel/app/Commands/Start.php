<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;

class Start extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        $this->log();

        $player = Player::where('user_id', 125641223544373248)->first();
        if(is_null($player))
        {
            echo PHP_EOL.'Execute Start';
            $newPlayer = new Player;
            $newPlayer->user_id = $this->message->author->id;
            $newPlayer->user_name = $this->message->author->user_name;
            $newPlayer->ban = false;
            $newPlayer->votes = 0;
            $newPlayer->save();   
            $newPlayer->addColony();
            //$this->message->channel->sendMessage('test executed');
            return "[Blabla Synopsis]\n\nPour afficher votre profile utilisez `!p`";
        }
        else
            return "Joueur déjà xistant\n\nPour afficher votre profile utilisez `!p`";
    }
}
