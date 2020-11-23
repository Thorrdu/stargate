<?php

namespace App\Commands;

use Illuminate\Support\Str;

class Flex extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            echo PHP_EOL.'Execute flex';

            if(count($this->args) < 2)
                return trans('generic.wrongParameter', [], $this->player->lang);

            $qtyToCheck = 0;
            if(Str::startsWith($this->args[1], 'all'))
                $qtyToCheck = 'all';
            elseif((int)$this->args[1] > 0)
                $qtyToCheck = (int)$this->args[1];

            $availableResources = config('stargate.resources');
            $availableResources[] = 'E2PZ';
            $availableResources[] = 'military';

            $resourceName = $this->args[0];
            if(Str::startsWith('e2pz',$resourceName) || Str::startsWith('zpm',$resourceName) || Str::startsWith('ZPM',$resourceName))
                $resourceName = 'E2PZ';
            foreach($availableResources as $availableResource)
            {
                if(Str::startsWith($availableResource,$resourceName) || Str::startsWith($availableResource,strtolower($resourceName)) || Str::startsWith($availableResource,strtoupper($resourceName)))
                {
                    $resFound = true;
                    $resourceName = $availableResource;
                }
            }
            if(!$resFound)
                return trans('stargate.unknownResource', ['resource' => $resourceName], $this->player->lang);
            else
            {
                if($qtyToCheck == 'all')
                    return trans('flex.flexSuccess', ['resource' => config('stargate.emotes.'.strtolower($resourceName))." ".ucfirst($resourceName), 'qty' => number_format($this->player->activeColony->$resourceName)], $this->player->lang);
                elseif($this->player->activeColony->$resourceName >= $qtyToCheck)
                    return trans('flex.flexSuccess', ['resource' => config('stargate.emotes.'.strtolower($resourceName))." ".ucfirst($resourceName), 'qty' => number_format($qtyToCheck)], $this->player->lang);
                else
                    return trans('flex.flexFail', [], $this->player->lang);
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

    }
}
