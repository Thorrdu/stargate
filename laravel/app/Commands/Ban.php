<?php

namespace App\Commands;

use App\Player;

class Ban extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if($this->message->author->id == 125641223544373248)
        {
            echo PHP_EOL.'Ban';
            $playerToBan = Player::where('user_id', $this->message->mentions[0]->id)->first();
            if(!is_null($playerToBan))
            {
                if($playerToBan->ban)
                {
                    $playerToBan->ban = false;
                    $playerToBan->save();
                    return "le ban de ".$this->message->mentions[0]->id.' est désormais levé';
                }
                else
                {
                    $playerToBan->ban = true;
                    $playerToBan->save();
                    return $this->message->mentions[0]->id.' est désormais bani';
                }
            }
            else
            {
                return 'Joueur non existant';
            }

        }
        else
            return "Vous n'avez pas la permission d'utiliser cette commande...";
    }
}
