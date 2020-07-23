<?php

namespace App\Commands;

use App\Player;

class Refresh extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            $this->player->colonies[0]->calcProd();
            return "Prod recalculée";
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
    }
}
