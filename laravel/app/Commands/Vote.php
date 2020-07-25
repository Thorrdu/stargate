<?php

namespace App\Commands;

class Vote extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        return "tu peux voter pour Stargate en utilisant ce lien: https://top.gg/bot/730815388400615455";
    }
}
