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
            {
                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                    ],
                    'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/disabledStargate.jpg'],
                    "title" => "Stargate",
                    "description" => trans('stargate.askBaseParameter', [], $this->player->lang),
                    'fields' => [
                    ],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];
                $this->message->channel->sendMessage('', false, $embed);
                return;
            }
            
            if(count($this->args) < 2)
            {
                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                    ],
                    'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/enabledStargate.jpg'],
                    "title" => "Stargate",
                    "description" => trans('stargate.askBaseParameter', [], $this->player->lang),
                    'fields' => [
                    ],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];
                $this->message->channel->sendMessage('', false, $embed);
                return;
            }

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
