<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Stargate extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Stargate';
            if($this->player->ban)
                return trans('generic.banned', [], $this->player->lang);

            return "Command under development";
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
