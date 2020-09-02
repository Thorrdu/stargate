<?php

namespace App\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Vote extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player) && $this->player->ban)
            return trans('generic.banned',[],$this->player->lang);

        $now = Carbon::now();
        if(!is_null($this->player->vote_available))
        {
            $availableDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->vote_available);
            if($availableDate > $now)
            {
                $nextVote = $now->diffForHumans($availableDate,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
                return trans('vote.voteTimer', ['time' => $nextVote], $this->player->lang);
            }
        }


        echo PHP_EOL.'Execute Invite';
        if(!is_null($this->player))
            return trans('vote.voteMessage',['link'=>'https://top.gg/bot/730815388400615455'], $this->player->lang);
        else
            return trans('vote.voteMessage',['link'=>'https://top.gg/bot/730815388400615455']);
    }
}
