<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;

class Research extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            try{
                echo PHP_EOL.'Execute Research';
                if($this->player->ban)
                    return 'Vous êtes banni...';
                $this->player->checkTechnology();
                $this->player->refresh();
                
                if(empty($this->args) || $this->args[0] == 'list')
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                        ],
                        "title" => 'Liste des technologies',
                        "description" => 'Pour commencer la recherche d\'une technologie utilisez `!research [Numéro]`',
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    $technologies = Technology::all();
                    foreach($technologies as $technology)
                    {
                        $wantedLevel = 1;
                        $currentLvl = $this->player->hasTechnology($technology);
                        if($currentLvl)
                            $wantedLevel += $currentLvl;

                        $buildingPrice = "";
                        $buildingPrices = $technology->getPrice($wantedLevel);
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($technology->$resource > 0)
                            {
                                if(!empty($buildingPrice))
                                    $buildingPrice .= " ";
                                $buildingPrice .= number_format(round($buildingPrices[$resource])).' '.ucfirst($resource);
                            }
                        }

                        $buildingTime = $technology->getTime($wantedLevel);

                        /** Application des bonus */
                        $buildingTime *= $this->player->getResearchBonus();

                        $buildingTime = gmdate("H:i:s", $buildingTime);

                        $displayedLvl = 0;
                        if($currentLvl)
                            $displayedLvl = $currentLvl;
                            
                        $conditionsValue = "";
                        $hasRequirements = true;
                        foreach($technology->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                            if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;

                            if(!empty($conditionsValue))
                                $conditionsValue .= " / ";
                            $conditionsValue .= $requiredTechnology->name.' - LVL '.$requiredTechnology->pivot->level;
                        }
                        foreach($technology->requiredBuildings as $requiredBuilding)
                        {
                            $currentLvlOwned = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                            if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                $hasRequirements = false;

                            if(!empty($conditionsValue))
                                $conditionsValue .= " / ";
                            $conditionsValue .= $requiredBuilding->name.' - LVL '.$requiredBuilding->pivot->level;
                        }
                        if(!empty($conditionsValue))
                            $conditionsValue = "\nCondition: ".$conditionsValue;

                        if($hasRequirements == true)
                        {
                            $embed['fields'][] = array(
                                'name' => $technology->id.' - '.$technology->name.' - LVL '.$displayedLvl,
                                'value' => 'Description: '.$technology->description."\nTemps: ".$buildingTime.$conditionsValue."\nPrix: ".$buildingPrice
                            );
                        }

                    }
        
                    if(empty($embed['fields']))
                        return 'Vous n\'avez débloqué aucune technologie actuellement...';

                    $this->message->channel->sendMessage('', false, $embed);
                }
                else
                {
                    $technologyId = (int)$this->args[0];
                    $technology = Technology::find($technologyId);
                    if(!is_null($technology))
                    {
                        //if recherche en cours, return
                        if(!is_null($this->player->active_technology_end))
                            return 'Une technologie est déjà en cours de recherche';

                        $wantedLvl = 1;
                        $currentLevel = $this->player->hasTechnology($technology);
                        if($currentLevel)
                            $wantedLvl += $currentLevel;

                        $hasEnough = true;
                        $technologyPrices = $technology->getPrice($wantedLvl);
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($technology->$resource > 0 && $technologyPrices[$resource] > $this->player->colonies[0]->$resource)
                                $hasEnough = false;
                        }

                        if(!$hasEnough)
                            return 'Vous ne possédez pas assez de ressource pour rechercher cette technologie.';

                        if( !is_null($this->player->colonies[0]->active_building_id) && $this->player->colonies[0]->active_building_id == 7)
                            return 'Votre centre de recherche est occupé...';

                        //Requirement
                        $hasRequirements = true;
                        foreach($technology->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvl = $this->player->hasTechnology($requiredTechnology);
                            if(!($currentLvl && $currentLvl >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;
                        }
                        foreach($technology->requiredBuildings as $requiredBuilding)
                        {
                            $currentLvl = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                            if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                                $hasRequirements = false;
                        }

                        if(!$hasRequirements)
                            return 'Vous ne possédez pas assez les pré-requis pour cette recherche.';



                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->startResearch($technology))->timestamp;
                        $buildingTime = gmdate("H:i:s", $endingDate - time());

                        return 'Recherche commencée, **'.$technology->name.' LVL '.$wantedLvl.'** sera terminé dans '.$buildingTime;

                    }
                }

            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                return $e->getMessage();
            }
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }
}
