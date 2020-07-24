<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use Carbon\Carbon;

class Colony extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Colony';

            //$table->enum('type', ['Energy', 'Production', 'Storage', 'Science', 'Military']);
            //$table->enum('production_type', ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'space', 'special']);

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => 'Colonie '.$this->player->colonies[0]->name,
                //"description" => 'Colonie '.$this->player->colonies[0]->name,
                //"description" => "",
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                    'text'  => 'Stargate',
                ),
            ];

            /*
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


            $table->integer('active_building_id')->unsigned()->nullable();
            */
            $resourcesValue = "";
            $productionValue = '';
            foreach (config('stargate.resources') as $resource)
            {
                if(!empty($resourcesValue))
                {
                    $resourcesValue .= "\n";
                    $productionValue .= "\n";
                }
                $resourcesValue .= ucfirst($resource).' '.number_format($this->player->colonies[0]->$resource).' / '.number_format($this->player->colonies[0]['storage_'.$resource]);
                $productionValue .= ucfirst($resource).' '.number_format($this->player->colonies[0]['production_'.$resource]).' / Heure';
            }

            if(!empty($resourcesValue))
            {
                $resourcesValue .= "\nEnergie ".($this->player->colonies[0]->energy_max - round($this->player->colonies[0]->energy_used)).' / '.$this->player->colonies[0]->energy_max;
                
                $resourcesValue .= "\nColons/Soldats ".round($this->player->colonies[0]->soldiers);
                $resourcesValue .= "\nE2PZ ".round($this->player->colonies[0]->E2PZ);
                $embed['fields'][] = array(
                                        'name' => 'Ressources',
                                        'value' => $resourcesValue,
                                        'inline' => true
                                    );

                $productionValue .= "\nE2PZ ".$this->player->colonies[0]->production_e2pz." / Semaine";
                $productionValue .= "\nColons/Soldats ".$this->player->colonies[0]->production_military." / Heure";
                $embed['fields'][] = array(
                                        'name' => 'Production',
                                        'value' => $productionValue,
                                        'inline' => true
                                    );
            }


            $prodBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == 'Production' || $value->type == "Energy";
            });
            $prodBuildingsValue = "";
            foreach($prodBuildings as $prodBuilding)
            {
                if(!empty($prodBuildingsValue))
                    $prodBuildingsValue .= "\n";
                $prodBuildingsValue .= $prodBuilding->name.' - LVL '.$prodBuilding->pivot->level;
            }
            if(!empty($prodBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => 'Bâtiments de production',
                                        'value' => $prodBuildingsValue,
                                        'inline' => true
                                    );
            }

            $scienceBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == "Science";
            });
            $scienceBuildingsValue = "";
            foreach($scienceBuildings as $scienceBuilding)
            {
                if(!empty($scienceBuildingsValue))
                    $scienceBuildingsValue .= "\n";
                $scienceBuildingsValue .= $scienceBuilding->name.' - LVL '.$scienceBuilding->pivot->level;
            }
            if(!empty($scienceBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => 'Bâtiments scientifiques',
                                        'value' => $scienceBuildingsValue,
                                        'inline' => true
                                    );
            }

            $militaryBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == "Military";
            });
            $militaryBuildingsValue = "";
            foreach($militaryBuildings as $militaryBuilding)
            {
                if(!empty($militaryBuildingsValue))
                    $militaryBuildingsValue .= "\n";
                $militaryBuildingsValue .= $militaryBuilding->name.' - LVL '.$militaryBuilding->pivot->level;
            }
            if(!empty($militaryBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => 'Bâtiments militaires',
                                        'value' => $militaryBuildingsValue,
                                        'inline' => true
                                    );
            }

            $storageBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == "Military";
            });
            $storageBuildingsValue = "";
            foreach($storageBuildings as $storageBuilding)
            {
                if(!empty($storageBuildingsValue))
                    $storageBuildingsValue .= "\n";
                $storageBuildingsValue .= $storageBuilding->name.' - LVL '.$storageBuilding->pivot->level;
            }
            if(!empty($storageBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => 'Entrepôts',
                                        'value' => $storageBuildingsValue,
                                        'inline' => true
                                    );
            }

            $technologyValue = "";
            foreach($this->player->technologies as $technology)
            {
                if(!empty($technologyValue))
                    $technologyValue .= "\n";
                $technologyValue .= $technology->name.' - LVL '.$technology->pivot->level;
            }
            if(!empty($technologyValue))
            {
                $embed['fields'][] = array(
                                        'name' => 'Technologies',
                                        'value' => $technologyValue,
                                        'inline' => true
                                    );
            }

            if(!is_null($this->player->colonies[0]->active_building_end)){
                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->colonies[0]->active_building_end)->timestamp;
                $buildingTime = gmdate("H:i:s", $buildingEnd - time());

                $currentLevel = $this->player->colonies[0]->hasBuilding($this->player->colonies[0]->activeBuilding);
                if(!$currentLevel)
                    $currentLevel = 0;
                $embed['fields'][] = array(
                    'name' => 'Construction en cours',
                    'value' => $this->player->colonies[0]->activeBuilding->name." - LVL ".($currentLevel+1)."\n".$buildingTime,
                    'inline' => true
                );
            }

            if(!is_null($this->player->active_technology_end)){
                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->active_technology_end)->timestamp;
                $buildingTime = gmdate("H:i:s", $buildingEnd - time());

                $currentLevel = $this->player->hasTechnology($this->player->activeTechnology);
                if(!$currentLevel)
                    $currentLevel = 0;
                $embed['fields'][] = array(
                    'name' => 'Recherche en cours',
                    'value' => $this->player->activeTechnology->name." - LVL ".($currentLevel+1)."\n".$buildingTime,
                    'inline' => true
                );
            }

            $embed['fields'][] = array(
                'name' => 'Bonus spécifiques',
                'value' => "ex: +20% Naq -20% Fer\n(Faire Type de colonie et image)",
                'inline' => true
            );

            //print_r($embed['fields']);
            //print_r($embed);

            $this->message->channel->sendMessage('', false, $embed);
        }       
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }
}
