<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use Carbon\Carbon;

class Build extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if(empty($this->args) || $this->args[0] == 'list')
            {
                echo PHP_EOL.'Execute Build';
                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                    ],
                    "title" => 'Liste des bâtiments',
                    "description" => 'Pour commencer la construction d\'un bâtiment utilisez `!build [Numéro]`',
                    'fields' => [],
                    'footer' => array(
                        //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                        'text'  => 'Stargate',
                    ),
                ];
    
                $buildings = Building::all();
                foreach($buildings as $building)
                {
                    $currentLevel = $this->player->colonies[0]->hasBuilding($building);
                    if(!$currentLevel)
                        $currentLevel = 0;

                    $buildingPrice = "";
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($building->$resource > 0)
                        {
                            if(!empty($buildingPrice))
                                $buildingPrice .= " ";

                            $ressPrice = $building->$resource;
                            if($currentLevel > 0)
                                $ressPrice = $building->$resource * pow($building->upgrade_coefficient, $currentLevel).' '.ucfirst($resource);

                            $buildingPrice .= round($ressPrice).' '.ucfirst($resource);
                        }
                    }
                    if($building->energy_base > 0)
                    {
                        $energyRequired = $building->energy_base;
                        if($currentLevel > 0)
                        {
                            $energyRequired = $building->energy_base * pow($building->energy_coefficient, $currentLevel);
                            if($currentLevel > 1)
                                $energyRequired -= $building->energy_base * pow($building->energy_coefficient, ($currentLevel-1));
                            else
                                $energyRequired -= $building->energy_base;
                        }
                        $buildingPrice .= " ".round($building->energy_base)." Energie";
                    }

                    $buildingTime = $building->time_base;
                    if($currentLevel > 0)    
                        $buildingTime = $building->time_base * pow($building->time_coefficient, $currentLevel);

                    $currentRobotic = $this->player->colonies[0]->hasBuilding(Building::find(6));
                    if($currentRobotic)
                        $buildingTime *= pow(0.9, $currentRobotic);

                    $buildingTime = gmdate("H:i:s", $buildingTime);

                    $embed['fields'][] = array(
                        'name' => $building->id.' - '.$building->name,
                        'value' => 'Description: '.$building->description."\nTemps: ".$buildingTime."\nCondition: /\nPrix: ".$buildingPrice
                    );
                }
    
                $this->message->channel->sendMessage('Build Embed', false, $embed);
            }
            else
            {
                $buildingId = (int)$this->args[0];
                $building = Building::find($buildingId);
                if(!is_null($building))
                {
                    //if construction en cours, return
                    if(!is_null($this->player->colonies[0]->active_building_end))
                        return 'Un bâtiment est déjà en construction sur cette colonie';

                    $coeficient = 1;
                    $currentLevel = $this->player->colonies[0]->hasBuilding($building);
                    if($currentLevel)
                        $coeficient += $currentLevel;

                    $buildingPrice = "";
                    $hasEnough = true;
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($building->$resource*$coeficient > $this->player->colonies[0]->$resource)
                            $hasEnough = false;
                    }

                    if($hasEnough)
                    {
                        $endingDate = $this->player->colonies[0]->startBuilding($building);
                        return 'Construction commencée, **'.$building->name.' LVL '.$coeficient.'** sera terminé le: '.$endingDate;
                    }
                    else
                    {
                        return 'Vous ne possédez pas assez de ressource pour construire ce bâtiment.';
                    }

                }
            }

            
        }
        return false;
    }
}
