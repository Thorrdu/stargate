<?php

namespace App\Commands;

class Invite extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if(!is_null($this->player) && $this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);
        }

        echo PHP_EOL.'Execute Invite';
        if(!is_null($this->player))
            return trans('invite.inviteMessage',['link'=>'https://discordapp.com/oauth2/authorize?client_id=730815388400615455&scope=bot&permissions=322624'], $this->player->lang);
        else
            return trans('invite.inviteMessage',['link'=>'https://discordapp.com/oauth2/authorize?client_id=730815388400615455&scope=bot&permissions=322624']);
    }
}
