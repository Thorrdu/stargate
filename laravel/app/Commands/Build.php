<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;

class Build extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Build';

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => 'Liste des bâtiments '.$this->player->colonies[0]->name,
                "description" => 'Pour commencer la construction d\'un bâtiment utilisez `!build [Numéro]`',
                'fields' => [],
                'footer' => array(
                    'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                    'text'  => 'Stargate',
                ),
            ];

            $buildings = Building::all();
            foreach($buildings as $building)
            {
                $coeficient = 1;
                $currentLevel = $this->player->colonies[0]->hasBuilding($building);
                if(!$currentLevel)
                    $coeficient += $currentLevel;

                $buildingPrice = "";
                foreach (config('stargate.resources') as $resource)
                {
                    if($this->player->colonies[0]->$resource > 0)
                    {
                        if(!empty($resourcesValue))
                            $buildingPrice .= " | ";
                        $buildingPrice .= ucfirst($resource).' '.round($this->player->colonies[0]->$resource)*$coeficient;
                    }
                }

                $embed['fields'][] = array(
                    'name' => $building->id.' - '.$building->name,
                    'value' => $building->description."\nPrix: ".$buildingPrice,
                    'inline' => true
                );
            }




            print_r($embed['fields']);

            $this->message->channel->sendMessage('Colony Embed', false, $embed);
        }
        return false;
    }
}
