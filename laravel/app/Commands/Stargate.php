<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Building;

class Stargate extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Stargate';
            if($this->player->ban)
                return trans('generic.banned', [], $this->player->lang);

            $researchCenter = Building::find(7);
            $centerLevel = $this->player->colonies[0]->hasBuilding($researchCenter);
            if(!$centerLevel || $centerLevel < 5)
                return trans('stargate.stargateShattered', [], $this->player->lang);
            
            if(count($this->args) < 2)
                return trans('stargate.askBaseParameter', [], $this->player->lang);

            if(preg_match('[0-9]{1,}:[0-9]{1,}:[0-9]{1,}', $this->args[1], $coordinates))
                return trans('stargate.unknownCoordinates', [], $this->player->lang);

            //Check Consommation E2PZ

            //Est-ce que la destination à une porte ?

            if(Str::startsWith('explore',$this->args[0]))
                return 'Under developement';

            if(Str::startsWith('trade',$this->args[0]))
                return 'Under developement';

            if(Str::startsWith('spy',$this->args[0]))
                return 'Under developement';
            
            if(Str::startsWith('attack',$this->args[0]))
                return 'Under developement';
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
