<?php

namespace App\Commands;

class Vote extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player) && $this->player->ban)
            return trans('generic.banned',[],$this->player->lang);

        if($this->player->captcha)
            return trans('generic.captchaMessage',[],$this->player->lang);

        echo PHP_EOL.'Execute Invite';
        if(!is_null($this->player))
            return trans('vote.voteMessage',['link'=>'https://top.gg/bot/730815388400615455'], $this->player->lang);
        else
            return trans('vote.voteMessage',['link'=>'https://top.gg/bot/730815388400615455']);
    }
}
