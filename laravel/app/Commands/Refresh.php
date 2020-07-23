<?php

namespace App\Commands;

use App\Player;

class Refresh extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        $this->player->colonies[0]->calcProd();
        return "Prod recalculée";
    }
}
