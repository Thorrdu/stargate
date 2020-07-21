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
        if(is_null($this->player))
        {
            echo PHP_EOL.'Execute Start';
            $newPlayer = new Player;
            $newPlayer->user_id = $this->player->id;
            $newPlayer->user_name = $this->message->author->user_name;
            $newPlayer->ban = false;
            $newPlayer->votes = 0;
            $newPlayer->save();   
            $newPlayer->addColony();
            return "[Blabla Synopsis]\n\nPour afficher votre profile utilisez `!p`";
        }
        else
            return "Joueur déjà créé\n\nPour afficher votre profile utilisez `!p`";
    }
}
