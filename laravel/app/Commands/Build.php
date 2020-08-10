<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

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
                        if(count($this->args) == 2 && Str::startsWith('confirm', $this->args[1]))
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
                                return trans('generic.missingRequirements', [], $this->player->lang);

                            $wantedLvl = 1;
                            $currentLvl = $this->player->colonies[0]->hasBuilding($building);
                            if($currentLvl)
                                $wantedLvl += $currentLvl;

                            //if construction en cours, return
                            if(!is_null($this->player->colonies[0]->active_building_end))
                            {
                                $now = Carbon::now();
                                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->colonies[0]->active_building_end);
                                $buildingTime = $now->diffForHumans($buildingEnd,[
                                    'parts' => 3,
                                    'short' => true, // short syntax as per current locale
                                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                ]);
                                //:level :name will be done in :time
                                return trans('building.alreadyBuilding', ['level' => $wantedLvl, 'name' => $this->player->colonies[0]->activeBuilding->name, 'time' => $buildingTime], $this->player->lang);
                            }

                            if(!is_null($building->level_max) && $wantedLvl > $building->level_max)
                            {
                                return trans('building.buildingMaxed', [], $this->player->lang);
                            }

                            if(($this->player->colonies[0]->space_max - $this->player->colonies[0]->space_used) <= 0)
                                return trans('building.missingSpace', [], $this->player->lang);
                            
                            $hasEnough = true;
                            $buildingPrices = $building->getPrice($wantedLvl);
                            $missingResString = "";
                            foreach (config('stargate.resources') as $resource)
                            {
                                if($building->$resource > 0 && $buildingPrices[$resource] > $this->player->colonies[0]->$resource)
                                {
                                    $hasEnough = false;
                                    $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($buildingPrices[$resource]-$this->player->colonies[0]->$resource));
                                }
                            }
                            if(!$hasEnough)
                                return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                            if($building->energy_base > 0)
                            {
                                $energyPrice = $building->getEnergy($wantedLvl);
                                $energyLeft = ($this->player->colonies[0]->energy_max - $this->player->colonies[0]->energy_used);
                                $missingEnergy = $energyPrice - $energyLeft;
                                if($missingEnergy > 0)
                                    return trans('building.notEnoughEnergy', ['missingEnergy' => $missingEnergy], $this->player->lang);
                            }

                            if( !is_null($this->player->active_technology_id) && $building->id == 7)
                                return trans('generic.busyBuilding', [], $this->player->lang);

                            $now = Carbon::now();
                            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->colonies[0]->startBuilding($building));
                            $buildingTime = $now->diffForHumans($endingDate,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                            return trans('building.buildingStarted', ['name' => $building->name, 'level' => $wantedLvl, 'time' => $buildingTime], $this->player->lang);
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
                                return trans('building.notYetDiscovered', [], $this->player->lang);
                            }

                            $wantedLvl = 1;
                            $currentLvl = $this->player->colonies[0]->hasBuilding($building);
                            if($currentLvl)
                                $wantedLvl += $currentLvl;
                

                            if(!is_null($building->level_max) && $wantedLvl > $building->level_max)
                            {
                                $buildingPrice = "Maxed";
                                $buildingTime = "Maxed";
                            }
                            else
                            {

                                $buildingPrice = "";
                                $buildingPrices = $building->getPrice($wantedLvl);
                                foreach (config('stargate.resources') as $resource)
                                {
                                    if($building->$resource > 0)
                                    {
                                        
                                        $buildingPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($buildingPrices[$resource]))."\n";
                                    }
                                }
                                if($building->energy_base > 0)
                                {
                                    $energyRequired = $building->getEnergy($wantedLvl);
                                    $buildingPrice .= config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang)." ".number_format(round($energyRequired))."\n";
                                }
                    
                                $buildingTime = $building->getTime($wantedLvl);
                                /** Application des bonus */
                                $buildingTime *= $this->player->colonies[0]->getBuildingBonus();
                                $now = Carbon::now();
                                $buildingEnd = $now->copy()->addSeconds($buildingTime);
                                $buildingTime = $now->diffForHumans($buildingEnd,[
                                    'parts' => 3,
                                    'short' => true, // short syntax as per current locale
                                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                ]);     
                            }
   
                
                            $displayedLvl = 0;
                            if($currentLvl)
                                $displayedLvl = $currentLvl;

                            $bonusString = "";
                            if(!is_null($building->energy_bonus))
                            {
                                $bonus = ($building->energy_bonus*100)-100;
                                $bonusString .= "+{$bonus}% ".config('stargate.emotes.energy')." ".trans('generic.produced', [], $this->player->lang)." ".trans('generic.produced', [], $this->player->lang)."\n";
                            }
                            if(!is_null($building->building_bonus))
                            {
                                $bonus = 100-($building->building_bonus*100);
                                $bonusString .= "-{$bonus}% ".config('stargate.emotes.productionBuilding')." ".trans('generic.buildingTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($building->technology_bonus))
                            {
                                $bonus = 100-($building->technology_bonus*100);
                                $bonusString .= "-{$bonus}% ".config('stargate.emotes.research')." ".trans('generic.researchTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($building->crafting_bonus))
                            {
                                $bonus = 100-($building->crafting_bonus*100);
                                $bonusString .= "-{$bonus}% ".config('stargate.emotes.productionBuilding')." ".trans('generic.craftingTime', [], $this->player->lang)."\n";
                            }
                            $productionString = $consoString = "";
                            if(!is_null($building->production_base))
                            {
                                if($building->type == "Energy")
                                {
                                    if($currentLvl)
                                        $productionString .= "Lvl ".$currentLvl." - ".round($building->getProductionEnergy($currentLvl))."\n";
                                    $productionString .= "Lvl ".($currentLvl+1)." - ".round($building->getProductionEnergy($currentLvl+1));
                                }
                                else
                                {
                                    if($building->slug == 'asuranfactory')
                                    {
                                        if($currentLvl)
                                            $productionString .= "Lvl ".$currentLvl." - ".round($building->getProductionE2PZ($currentLvl))."\n";
                                        $productionString .= "Lvl ".($currentLvl+1)." - ".round($building->getProductionE2PZ($currentLvl+1));
                                    }
                                    else
                                    {
                                        if($currentLvl)
                                            $productionString .= "Lvl ".$currentLvl." - ".round($building->getProduction($currentLvl))."\n";
                                        $productionString .= "Lvl ".($currentLvl+1)." - ".round($building->getProduction($currentLvl+1));
                                    }

                                }

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
                                "title" => 'Lvl '.$displayedLvl.' - '.$building->name,
                                "description" => trans('building.howTo', ['id' => $building->id, 'slug' => $building->slug, 'description' => $building->description], $this->player->lang),
                                'fields' => [
                                    [
                                        'name' => trans('generic.info', [], $this->player->lang),
                                        'value' => "ID: ".$building->id."\n"."Slug: `".$building->slug."`",
                                        'inline' => true
                                    ],
                                    [
                                        'name' => trans('generic.bonusPerLvl', [], $this->player->lang),
                                        'value' => $bonusString,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => trans('generic.production', [], $this->player->lang),
                                        'value' => $productionString,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => trans('generic.consumption', [], $this->player->lang),
                                        'value' => $consoString,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => trans('generic.price', [], $this->player->lang),
                                        'value' => $buildingPrice,
                                        'inline' => true
                                    ],
                                    [
                                        'name' => trans('generic.duration', [], $this->player->lang),
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
                        return trans('building.unknownBuilding', [], $this->player->lang);
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
            "title" => trans('building.buildingList', [], $this->player->lang),
            "description" => trans('building.genericHowTo', [], $this->player->lang),
            'fields' => [],
            'footer' => array(
                //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $building)
        {
            $wantedLvl = 1;
            $currentLvl = $this->player->colonies[0]->hasBuilding($building);
            if($currentLvl)
                $wantedLvl += $currentLvl;


            if(!is_null($building->level_max) && $wantedLvl > $building->level_max)
            {
                $buildingPrice = "/";
                $buildingTime = 'Maxed';
            }
            else
            {
                $buildingPrice = "";
                $buildingPrices = $building->getPrice($wantedLvl);
                foreach (config('stargate.resources') as $resource)
                {
                    if($building->$resource > 0)
                    {
                        if(!empty($buildingPrice))
                            $buildingPrice .= " ";
                        $buildingPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($buildingPrices[$resource]));
                    }
                }
                if($building->energy_base > 0)
                {
                    $energyRequired = $building->getEnergy($wantedLvl);
                    $buildingPrice .= " ".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang)." ".number_format(round($energyRequired));
                }
                
                $buildingTime = $building->getTime($wantedLvl);

                /** Application des bonus */
                $buildingTime *= $this->player->colonies[0]->getBuildingBonus();

                $now = Carbon::now();
                $buildingEnd = $now->copy()->addSeconds($buildingTime);
                $buildingTime = $now->diffForHumans($buildingEnd,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);      
            }


            $displayedLvl = 0;
            if($currentLvl)
                $displayedLvl = $currentLvl;

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

            if($hasRequirements == true)
            {
                $embed['fields'][] = array(
                    'name' => $building->id.' - '.$building->name.' - Lvl '.$displayedLvl,
                    'value' => "\nSlug: `".$building->slug."`\n - ".trans('generic.duration', [], $this->player->lang).": ".$buildingTime."\n".trans('generic.price', [], $this->player->lang).": ".$buildingPrice,
                    'inline' => true
                );
            }
            else
            {
                $embed['fields'][] = array(
                    'name' => trans('building.hiddenBuilding', [], $this->player->lang),
                    'value' => trans('building.unDiscovered', [], $this->player->lang),
                    'inline' => true
                );
            }
        }

        return $embed;
    }

}
