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
            try{
            $this->player->colonies[0]->checkColony();
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
                
                $resourcesValue .= "\nClônes ".round($this->player->colonies[0]->clones);
                $resourcesValue .= "\nE2PZ ".round($this->player->colonies[0]->E2PZ);
                $embed['fields'][] = array(
                                        'name' => 'Ressources',
                                        'value' => $resourcesValue,
                                        'inline' => true
                                    );

                $productionValue .= "\nE2PZ ".$this->player->colonies[0]->production_e2pz." / Semaine";
                $productionValue .= "\nClônes ".$this->player->colonies[0]->production_military." / Heure";
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
                return $value->type == "Storage";
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

            if(count($this->player->colonies[0]->units) > 0)
            {
                $unitsString = '';
                foreach($this->player->colonies[0]->units as $unit)
                {
                    $unitsString .= $unit->name." - ".number_format($unit->pivot->number)."\n";
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

            //print_r($embed['fields']);
            //print_r($embed);

            $this->message->channel->sendMessage('', false, $embed);

            }catch(\Exception $e)
            {
                return $e->getMessage();
            }
        }       
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }
}
