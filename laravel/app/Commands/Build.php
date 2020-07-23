<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
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
                    $wantedLevel = 1;
                    $currentLvl = $this->player->colonies[0]->hasBuilding($building);
                    if($currentLvl)
                        $wantedLevel += $currentLvl;

                    $buildingPrice = "";
                    $buildingPrices = $building->getPrice($wantedLevel);
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($building->$resource > 0)
                        {
                            if(!empty($buildingPrice))
                                $buildingPrice .= " ";
                            $buildingPrice .= number_format(round($buildingPrices[$resource])).' '.ucfirst($resource);
                        }
                    }
                    if($building->energy_base > 0)
                    {
                        $energyRequired = $building->getEnergy($wantedLevel);
                        if($wantedLevel > 1)
                            $energyRequired -= $building->getEnergy($wantedLevel - 1);
                        $buildingPrice .= " ".number_format(round($energyRequired))." Energie";
                    }

                    $buildingTime = $building->getTime($wantedLevel);

                    /** Application des bonus */
                    $buildingTime *= $this->player->colonies[0]->getBuildingBonus();

                    $buildingTime = gmdate("H:i:s", $buildingTime);

                    $displayedLvl = 0;
                    if($currentLvl)
                        $displayedLvl = $currentLvl;

                    $embed['fields'][] = array(
                        'name' => $building->id.' - '.$building->name.' - LVL '.$displayedLvl,
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

                    $wantedLvl = 0;
                    $currentLevel = $this->player->colonies[0]->hasBuilding($building);
                    if($currentLevel)
                        $wantedLvl += $currentLevel;

                    $hasEnough = true;
                    $buildingPrices = $building->getPrice($wantedLvl);
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($building->$resource > 0 && $buildingPrices[$resource] > $this->player->colonies[0]->$resource)
                            $hasEnough = false;
                    }

                    if($hasEnough)
                    {
                        $endingDate = $this->player->colonies[0]->startBuilding($building);
                        return 'Construction commencée, **'.$building->name.' LVL '.$wantedLvl.'** sera terminé le: '.$endingDate;
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
