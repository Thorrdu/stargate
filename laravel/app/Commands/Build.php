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
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $buildingList;
    
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);
            $this->player->colonies[0]->checkBuilding();

            try{
                if(empty($this->args) || $this->args[0] == 'list')
                {
                    echo PHP_EOL.'Execute Build';
                    $this->buildingList = Building::all();      
                    
                    $this->page = 1;
                    $this->maxPage = ceil($this->buildingList->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react('⏪')->then(function(){ 
                            $this->paginatorMessage->react('◀️')->then(function(){ 
                                $this->paginatorMessage->react('▶️')->then(function(){ 
                                    $this->paginatorMessage->react('⏩');
                                });
                            });
                        });
    
                        $this->listner = function ($messageReaction) {
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == '⏪')
                                {
                                    $this->page = 1;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
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
                                elseif($messageReaction->emoji->name == '⏩')
                                {
                                    $this->page = $this->maxPage;
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
                    $building = Building::where('id', (int)$this->args[0])->orWhere('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($building))
                    {
                        if(count($this->args) == 2 && in_array($this->args[1],array('conf','confirm')))
                        {
                            //Requirement
                            $hasRequirements = true;
                            foreach($building->requiredTechnologies as $requiredTechnology)
                            {
                                $currentLvl = $this->player->hasTechnology($requiredTechnology);
                                if(!($currentLvl && $currentLvl >= $requiredTechnology->pivot->level))
                                    $hasRequirements = false;
                            }
                            foreach($building->requiredBuildings as $requiredBuilding)
                            {
                                $currentLvl = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                                if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }
                            if(!$hasRequirements)
                                return 'Vous ne possédez pas assez les pré-requis du bâtiment.';

                            //if construction en cours, return
                            if(!is_null($this->player->colonies[0]->active_building_end))
                                return 'Un bâtiment est déjà en construction sur cette colonie';

                            if(($this->player->colonies[0]->space_max - $this->player->colonies[0]->space_used) <= 0)
                                return 'Espace insufisant pour construire un nouveau bâtiment.';
                            
                            $wantedLvl = 1;
                            $currentLvl = $this->player->colonies[0]->hasBuilding($building);
                            if($currentLvl)
                                $wantedLvl += $currentLvl;

                            $hasEnough = true;
                            $buildingPrices = $building->getPrice($wantedLvl);
                            foreach (config('stargate.resources') as $resource)
                            {
                                if($building->$resource > 0 && $buildingPrices[$resource] > $this->player->colonies[0]->$resource)
                                    $hasEnough = false;
                            }
                            if(!$hasEnough)
                                return 'Vous ne possédez pas assez de ressource pour construire ce bâtiment.';

                            if($building->energy_base > 0)
                            {
                                $energyPrice = $building->getEnergy($wantedLvl);
                                if(($this->player->colonies[0]->energy_max - $this->player->colonies[0]->energy_used) < $energyPrice)
                                    return "Vous ne possédez pas assez d'énergie pour allimenter ce bâtiment.";
                            }

                            if( !is_null($this->player->active_technology_id) && $building->id == 7)
                                return 'Votre centre de recherche est occupé...';

                            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->colonies[0]->startBuilding($building))->timestamp;
                            $buildingTime = gmdate("H:i:s", $endingDate - time());

                            return 'Construction commencée, **'.$building->name.' LVL '.$wantedLvl.'** sera terminé dans '.$buildingTime;
                        }
                        else
                        {
                            $hasRequirements = true;
                            foreach($building->requiredTechnologies as $requiredTechnology)
                            {
                                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                    $hasRequirements = false;
                            }
                            foreach($building->requiredBuildings as $requiredBuilding)
                            {
                                $currentLvlOwned = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }
                            if(!$hasRequirements)
                            {
                                return "Vous n'avez pas encore découvert ce bâtiment.";
                            }

                            $wantedLvl = 1;
                            $currentLvl = $this->player->colonies[0]->hasBuilding($building);
                            if($currentLvl)
                                $wantedLvl += $currentLvl;
                
                            $buildingPrice = "";
                            $buildingPrices = $building->getPrice($wantedLvl);
                            foreach (config('stargate.resources') as $resource)
                            {
                                if($building->$resource > 0)
                                {
                                    $buildingPrice .= number_format(round($buildingPrices[$resource])).' '.ucfirst($resource)."\n";
                                }
                            }
                            if($building->energy_base > 0)
                            {
                                $energyRequired = $building->getEnergy($wantedLvl);
                                $buildingPrice .= " ".number_format(round($energyRequired))." Energie";
                            }
                
                            $buildingTime = $building->getTime($wantedLvl);
                            /** Application des bonus */
                            $buildingTime *= $this->player->colonies[0]->getBuildingBonus();
                            $buildingTime = gmdate("H:i:s", $buildingTime);
                
                            $displayedLvl = 0;
                            if($currentLvl)
                                $displayedLvl = $currentLvl;

                            $bonusString = "";
                            if(!is_null($building->energy_bonus))
                            {
                                $bonus = ($building->energy_bonus*100)-100;
                                $bonusString .= "+{$bonus}% Energie produite\n";
                            }
                            if(!is_null($building->building_bonus))
                            {
                                $bonus = 100-($building->building_bonus*100);
                                $bonusString .= "-{$bonus}% Temps de construction\n";
                            }
                            if(!is_null($building->technology_bonus))
                            {
                                $bonus = 100-($building->technology_bonus*100);
                                $bonusString .= "-{$bonus}% Temps de recherche\n";
                            }
                            $productionString = $consoString = "";
                            if(!is_null($building->production_base))
                            {
                                if($currentLvl)
                                    $productionString .= "Lvl ".$currentLvl." - ".round($building->getProduction($currentLvl))."\n";
                                $productionString .= "Lvl ".($currentLvl+1)." - ".round($building->getProduction($currentLvl+1));
                            }
                            if(!is_null($building->energy_base))
                            {
                                if($currentLvl)
                                    $consoString .= "Lvl ".$currentLvl." - ".$building->getEnergy($currentLvl)."\n";
                                $consoString .= "Lvl ".($currentLvl+1)." - ".$building->getEnergy($currentLvl+1);
                            }
                            if(empty($productionString))
                                $productionString = "/";
                            if(empty($bonusString))
                                $bonusString = "/";
                            if(empty($consoString))
                                $consoString = "/";
                            $embed = [
                                'author' => [
                                    'name' => $this->player->user_name,
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                                ],
                                "title" => $building->name.' - LVL '.$displayedLvl,
                                "description" => "Construire avec `!build {$building->id} confirm` ou `!build {$building->slug} confirm`\n\n".$building->description,
                                'fields' => [
                                    [
                                        'name' => "Info",
                                        'value' => "ID: ".$building->id."\n"."Slug: `".$building->slug."`",
                                        'inline' => true
                                    ],
                                    [
                                        'name' => "Bonus par Lvl",
                                        'value' => $bonusString,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => "Production",
                                        'value' => $productionString,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => "Consommation",
                                        'value' => $consoString,
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

                            $this->message->channel->sendMessage('', false, $embed);
                        }
                    }
                    else
                        return 'Bâtiment inconnu';
                }
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                return $e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }

    public function getPage()
    {
        $displayList = $this->buildingList->skip(5*($this->page -1))->take(5);
        
        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
            ],
            "title" => 'Liste des bâtiments',
            "description" => "Pour voir le détail d'un bâtiment: `!build [ID/Slug]`\nPour commencer la construction d\'un bâtiment utilisez `!build [ID/Slug] confirm`\n",
            'fields' => [],
            'footer' => array(
                //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                'text'  => 'Stargate - Page '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $building)
        {
            $wantedLvl = 1;
            $currentLvl = $this->player->colonies[0]->hasBuilding($building);
            if($currentLvl)
                $wantedLvl += $currentLvl;

            $buildingPrice = "";
            $buildingPrices = $building->getPrice($wantedLvl);
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
                $energyRequired = $building->getEnergy($wantedLvl);
                $buildingPrice .= " ".number_format(round($energyRequired))." Energie";
            }

            $buildingTime = $building->getTime($wantedLvl);

            /** Application des bonus */
            $buildingTime *= $this->player->colonies[0]->getBuildingBonus();

            $buildingTime = gmdate("H:i:s", $buildingTime);

            $displayedLvl = 0;
            if($currentLvl)
                $displayedLvl = $currentLvl;

            //$conditionsValue = "";
            $hasRequirements = true;
            foreach($building->requiredTechnologies as $requiredTechnology)
            {
                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                    $hasRequirements = false;

                /*if(!empty($conditionsValue))
                    $conditionsValue .= " / ";
                $conditionsValue .= $requiredTechnology->name.' - LVL '.$requiredTechnology->pivot->level;*/
            }
            foreach($building->requiredBuildings as $requiredBuilding)
            {
                $currentLvlOwned = $this->player->colonies[0]->hasBuilding($requiredBuilding);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                    $hasRequirements = false;

                /*if(!empty($conditionsValue))
                    $conditionsValue .= " / ";
                $conditionsValue .= $requiredBuilding->name.' - LVL '.$requiredBuilding->pivot->level;*/
            }
            /*if(!empty($conditionsValue))
                $conditionsValue = "\nCondition: ".$conditionsValue;*/

            if($hasRequirements == true)
            {
                $embed['fields'][] = array(
                    'name' => $building->id.' - '.$building->name.' - LVL '.$displayedLvl,
                    'value' => "\nSlug: `".$building->slug."`\n - Temps: ".$buildingTime."\nPrix: ".$buildingPrice,
                    'inline' => true
                );
            }
            else
            {
                $embed['fields'][] = array(
                    'name' => '-- Bâtiment Caché --',
                    'value' => 'Non découvert.',
                    'inline' => true
                );
            }
        }

        return $embed;
    }

}
