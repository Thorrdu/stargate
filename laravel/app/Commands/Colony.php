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
            if($this->player->ban)
                return 'Vous êtes banni...';
            $this->player->colonies[0]->checkColony();
            $this->player->refresh();

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => 'Colonie '.$this->player->colonies[0]->name,
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];

            $resourcesValue = "";
            $productionValue = '';
            $storageValue = "";
            foreach (config('stargate.resources') as $resource)
            {
                if(!empty($resourcesValue))
                {
                    $resourcesValue .= "\n";
                    $productionValue .= "\n";
                }
                $resourcesValue .= config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($this->player->colonies[0]->$resource)." (".number_format($this->player->colonies[0]['production_'.$resource])."/H)";
                //$productionValue .= number_format($this->player->colonies[0]['production_'.$resource]).' '.ucfirst($resource).' / Heure';
                $storageValue .= number_format($this->player->colonies[0]['storage_'.$resource]).' '.ucfirst($resource)."\n";
            }

            if(!empty($resourcesValue))
            {
                $resourcesValue .= "\n".config('stargate.emotes.energy')." Energie: ".($this->player->colonies[0]->energy_max - round($this->player->colonies[0]->energy_used)).' / '.$this->player->colonies[0]->energy_max;
                $resourcesValue .= "\n".config('stargate.emotes.clone')." Clônes: ".round($this->player->colonies[0]->clones)." (".$this->player->colonies[0]->production_military."/H)";
                $resourcesValue .= "\n".config('stargate.emotes.e2pz')." E2PZ: ".round($this->player->colonies[0]->E2PZ)." (".$this->player->colonies[0]->production_e2pz."/Sem)";
                $embed['fields'][] = array(
                                        'name' => config('stargate.emotes.production')." Ressources",
                                        'value' => $resourcesValue,
                                        'inline' => true
                                    );

                //$productionValue .= "\n".$this->player->colonies[0]->production_e2pz." E2PZ / Semaine";
                //$productionValue .= "\n".$this->player->colonies[0]->production_military." Clônes / Heure";
                /*$embed['fields'][] = array(
                                        'name' => 'Production',
                                        'value' => $productionValue,
                                        'inline' => true
                                    );*/
            }


            $prodBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == 'Production' || $value->type == "Energy";
            });
            $prodBuildingsValue = "";
            foreach($prodBuildings as $prodBuilding)
            {
                if(!empty($prodBuildingsValue))
                    $prodBuildingsValue .= "\n";
                $prodBuildingsValue .= 'Lvl '.$prodBuilding->pivot->level.' - '.$prodBuilding->name;
            }
            if(!empty($prodBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => config('stargate.emotes.productionBuilding')." Bâtiments de production",
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
                $scienceBuildingsValue .= 'Lvl '.$scienceBuilding->pivot->level.' - '.$scienceBuilding->name;
            }
            if(!empty($scienceBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => config('stargate.emotes.research').' Bâtiments scientifiques',
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
                $militaryBuildingsValue .= 'Lvl '.$militaryBuilding->pivot->level.' - '.$militaryBuilding->name;
            }
            if(!empty($militaryBuildingsValue))
            {
                $embed['fields'][] = array(
                                        'name' => config('stargate.emotes.military').' Bâtiments militaires',
                                        'value' => $militaryBuildingsValue,
                                        'inline' => true
                                    );
            }
            /*
            $storageBuildings = $this->player->colonies[0]->buildings->filter(function ($value) {
                return $value->type == "Storage";
            });
            $storageBuildingsValue = "";
            foreach($storageBuildings as $storageBuilding)
            {
                if(!empty($storageBuildingsValue))
                    $storageBuildingsValue .= "\n";
                $storageBuildingsValue .= 'Lvl '.$storageBuilding->pivot->level.' '.$storageBuilding->pivot->level;
            }*/

            if(!empty($storageValue))
            {
                $storageValue = "\nEspace: ".($this->player->colonies[0]->space_max - $this->player->colonies[0]->space_used).' / '.$this->player->colonies[0]->space_max."\n".$storageValue;

                $embed['fields'][] = array(
                                        'name' => config('stargate.emotes.storage').' Capacité des Entrepôts',
                                        'value' => $storageValue,
                                        'inline' => true
                                    );
            }

            $technologyValue = "";
            foreach($this->player->technologies as $technology)
            {
                if(!empty($technologyValue))
                    $technologyValue .= "\n";
                $technologyValue .= 'Lvl '.$technology->pivot->level.' - '.$technology->name;
            }
            if(!empty($technologyValue))
            {
                $embed['fields'][] = array(
                                        'name' => config('stargate.emotes.research').' Technologies',
                                        'value' => $technologyValue,
                                        'inline' => true
                                    );
            }

            if(count($this->player->colonies[0]->units) > 0)
            {
                $unitsString = '';
                foreach($this->player->colonies[0]->units as $unit)
                {
                    $unitsString .= number_format($unit->pivot->number).' '.$unit->name."\n";
                }
                $embed['fields'][] = array(
                                        'name' => 'Unités',
                                        'value' => $unitsString,
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
                    'value' => "Lvl ".($currentLevel+1)." - ".$this->player->colonies[0]->activeBuilding->name."\n".$buildingTime,
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
                    'value' => "Lvl ".($currentLevel+1)." - ".$this->player->activeTechnology->name."\n".$buildingTime,
                    'inline' => true
                );
            }

            //print_r($embed['fields']);
            //print_r($embed);

            $this->message->channel->sendMessage('', false, $embed);
        }       
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }
}
