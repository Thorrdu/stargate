<?php

namespace App\Commands;

use App\Player;

class Start extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(is_null($this->player))
        {
            echo PHP_EOL.'Execute Start';
            $newPlayer = new Player;
            $newPlayer->user_id = $this->message->author->id;
            $newPlayer->user_name = $this->message->author->user_name;
            $newPlayer->ban = false;
            $newPlayer->votes = 0;
            $newPlayer->save();   
            $newPlayer->addColony();
            return "[Blabla Synopsis]\n\nPour afficher votre colonie utilisez `!colony` (ou !c)";
        }
        elseif($this->player->ban)
            return 'Vous êtes banni...';
        else
            return "Joueur déjà créé\n\nPour afficher votre profile utilisez `!colony` (ou !c)";
    }
}
