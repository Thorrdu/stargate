<?php

namespace App\Commands;

class Invite extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        echo PHP_EOL.'Execute Invite';
        if(!is_null($this->player) && $this->player->ban)
            return 'Vous êtes banni...';
        return "pour inviter Stargate sur votre serveur, utilisez ce lien: https://discordapp.com/oauth2/authorize?client_id=730815388400615455&scope=bot&permissions=322624";
    }
}
