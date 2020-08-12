<?php

namespace App\Commands;

use App\Player;

class Ban extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
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
                        return trans('ban.banLift', ['name' => $this->message->mentions[0]->username], $this->player->lang);
                    }
                    else
                    {
                        $playerToBan->ban = true;
                        $playerToBan->save();
                        return trans('ban.banApplied', ['name' => $this->message->mentions[0]->username], $this->player->lang);
                    }
                }
                else
                    return trans('generic.unknownPlayer', [], $this->player->lang);
            }
            else
                return trans('generic.missingPerm', [], $this->player->lang);
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
