<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;

class Colony extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Colony';

            //$table->enum('type', ['Energy', 'Production', 'Storage', 'Science', 'Military']);
            //$table->enum('production_type', ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'space', 'special']);

            $prodBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == 'Production';
            });

            $prodBuildingsValue = "";
            foreach($prodBuildings as $prodBuilding)
            {
                if(!empty($prodBuildingsValue))
                    $prodBuildingsValue .= "\n";
                $prodBuildingsValue .= "\n".$prodBuilding->building->name.' | LVL '.$prodBuilding->pivot->level;
            }
            if(!is_null($this->player->colonies[0]->active_building_end))
                $buildingEnd = $this->player->colonies[0]->active_building_end;

            $resourcesValue = '';
            foreach (config('stargate.resources') as $resource)
            {
                if(!empty($resourcesValue))
                    $resourcesValue .= "\n";
                $resourcesValue .= $this->player->colonies[0]->$resource;
            }



            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                //"title" => "",
                //"description" => "",
                'fields' =>array(
                    '0' => array(
                        'name' => 'Ressources',
                        'value' => $resourcesValue,
                        'inline' => true
                    ),
                    '1' => array(
                        'name' => 'Production',
                        'value' => 'Fer lalala',
                        'inline' => true
                    ),
                    '2' => array(
                        'name' => 'Bâtiments de production',
                        'value' => $prodBuildingsValue,
                        'inline' => true
                    ),
                    '3' => array(
                        'name' => 'Bâtiments militaires',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),
                    '4' => array(
                        'name' => 'Bâtiments scientifiques',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),
                    '5' => array(
                        'name' => 'Bâtiments de stockage',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),

                ),
                'footer' => array(
                    'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                    'text'  => 'Stargate',
                ),
            ];
            print_r($embed);

            $this->message->channel->sendMessage('Colony Embed', false, $embed);
        }
        return false;
    }
}
