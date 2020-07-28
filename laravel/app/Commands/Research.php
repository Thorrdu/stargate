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
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $researchList;

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
                    $this->researchList = Technology::all();

                    $this->page = 1;
                    $this->maxPage = ceil($this->researchList->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react('◀️')->then(function(){ 
                            $this->paginatorMessage->react('▶️');
                        });
    
                        $this->listner = function ($messageReaction) {
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                {
                                    $this->page--;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                {
                                    $this->page++;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                            }
                        };
                        $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                    });
                }
                else
                {
                    $technology = Technology::where('id', (int)$this->args[0])->orWhere('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($technology))
                    {
                        if(count($this->args) == 2 && in_array($this->args[1],array('conf','confirm')))
                        {
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

                            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->startResearch($technology))->timestamp;
                            $buildingTime = gmdate("H:i:s", $endingDate - time());

                            return 'Recherche commencée, **'.$technology->name.' LVL '.$wantedLvl.'** sera terminé dans '.$buildingTime;
                        }
                        else
                        {
                            $hasRequirements = true;
                            foreach($technology->requiredTechnologies as $requiredTechnology)
                            {
                                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                    $hasRequirements = false;
                            }
                            foreach($technology->requiredBuildings as $requiredBuilding)
                            {
                                $currentLvlOwned = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }

                            if(!$hasRequirements)
                            {
                                return "Vous n'avez pas encore découvert cette technologie.";
                            }

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
                                    $buildingPrice .= number_format(round($buildingPrices[$resource])).' '.ucfirst($resource)."\n";
                                }
                            }
                
                            $buildingTime = $technology->getTime($wantedLevel);
                            
                            /** Application des bonus */
                            $buildingTime *= $this->player->colonies[0]->getResearchBonus();
                
                            $buildingTime = gmdate("H:i:s", $buildingTime);
                
                            $displayedLvl = 0;
                            if($currentLvl)
                                $displayedLvl = $currentLvl;

                            $bonusString = "";
                            if(!is_null($technology->energy_bonus))
                                $bonusString += "Energie produite: +{($technology->energy_bonus*100)}%\n";
                            if(!is_null($technology->building_bonus))
                                $bonusString += "Temps de construction des bâtiments: -{($technology->building_bonus*100)}%\n";
                            if(!is_null($technology->technology_bonus))
                                $bonusString += "Temps de recherche: -{($technology->technology_bonus*100)}%\n";

                            $embed = [
                                'author' => [
                                    'name' => $this->player->user_name,
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                                ],
                                "title" => $technology->name.' - LVL '.$displayedLvl,
                                "description" => $technology->description,
                                'fields' => [
                                    [
                                        'name' => "Info",
                                        'value' => "ID: ".$technology->id."\n"."Slug: ".$technology->slug,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => "Bonus",
                                        'value' => $bonusString,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => "Prix",
                                        'value' => $buildingPrice,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => "Durée de recherche",
                                        'value' => $buildingTime,
                                        'inline' => true
                                    ]
                                ],
                                'footer' => array(
                                    'text'  => 'Stargate',
                                ),
                            ];
                        }
                    }
                    else
                        return 'Technologie inconnue...';
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

    public function getPage()
    {
        $displayList = $this->researchList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
            ],
            "title" => 'Liste des technologies',
            "description" => 'Pour commencer la recherche d\'une technologie utilisez `!research [Numéro]`',
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - Page '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $technology)
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
            $buildingTime *= $this->player->colonies[0]->getResearchBonus();

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
            /*if(!empty($conditionsValue))
                $conditionsValue = "\nCondition: ".$conditionsValue;*/

            if($hasRequirements == true)
            {
                $embed['fields'][] = array(
                    'name' => $technology->id.' - '.$technology->name.' - LVL '.$displayedLvl,
                    'value' => 'Description: '.$technology->description."\nSlug: ".$technology->slug."\nTemps: ".$buildingTime.$conditionsValue."\nPrix: ".$buildingPrice
                );
            }
            else
            {
                $embed['fields'][] = array(
                    'name' => '-- Technologie Cachée --',
                    'value' => 'Vous n\'avez pas encore découvert cette technologie.'
                );
            }
        }
        return $embed;
    }
}
