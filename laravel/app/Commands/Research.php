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
                    return trans('generic.banned',[],$this->player->lang);
                
                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);
                    
                /*$this->player->checkTechnology();
                $this->player->refresh();*/

                $this->player->activeColony->checkColony();
                $this->player->refresh();
                
                if(empty($this->args) || Str::startsWith('list', $this->args[0]))
                {
                    $this->researchList = Technology::all();

                    $this->page = 1;
                    $this->maxPage = ceil($this->researchList->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react('⏪')->then(function(){ 
                            $this->paginatorMessage->react('◀️')->then(function(){ 
                                $this->paginatorMessage->react('▶️')->then(function(){ 
                                    $this->paginatorMessage->react('⏩')->then(function(){
                                        $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                    });
                                });
                            });
                        });
    
                        $this->listner = function ($messageReaction) {

                            ${'listnerNameRes'.Str::random(10)} = 55;
                            if($this->maxTime < time())
                            {
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->lang), null);
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                            }
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->lang), null);
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                }
                                elseif($messageReaction->emoji->name == '⏪')
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
                    $technology = Technology::where('id', (int)$this->args[0])->orWhere('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($technology))
                    {
                        if(count($this->args) == 2 && Str::startsWith('confirm', $this->args[1]))
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
                                $currentLvl = $this->player->activeColony->hasBuilding($requiredBuilding);
                                if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }

                            if(!$hasRequirements)
                                return trans('generic.missingRequirements', [], $this->player->lang);

                            $wantedLvl = 1;
                            $currentLevel = $this->player->hasTechnology($technology);
                            if($currentLevel)
                                $wantedLvl += $currentLevel;

                            //if research en cours, return
                            if(!is_null($this->player->active_technology_end))
                            {
                                $wantedLvl = 1;
                                $currentLevel = $this->player->hasTechnology($this->player->activeTechnology);
                                if($currentLevel)
                                    $wantedLvl += $currentLevel;

                                $now = Carbon::now();
                                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->active_technology_end);
                                $buildingTime = $now->diffForHumans($buildingEnd,[
                                    'parts' => 3,
                                    'short' => true, // short syntax as per current locale
                                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                ]);
                                //:level :name will be done in :time
                                return trans('research.alreadyResearching', ['level' => $wantedLvl, 'name' => trans('research.'.$this->player->activetechnology->slug.'.name', [], $this->player->lang), 'time' => $buildingTime], $this->player->lang);
                            }

                            $hasEnough = true;
                            $technologyPrices = $technology->getPrice($wantedLvl);
                            $missingResString = "";
                            foreach (config('stargate.resources') as $resource)
                            {
                                if($technology->$resource > 0 && $technologyPrices[$resource] > $this->player->activeColony->$resource)
                                {
                                    $hasEnough = false;
                                    $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($technologyPrices[$resource]-$this->player->activeColony->$resource));
                                }
                            }

                            if(!$hasEnough)
                                return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                            if( !is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 7 )
                                return trans('generic.busyBuilding', [], $this->player->lang);

                            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->startResearch($technology));
                            $now = Carbon::now();
                            $buildingTime = $now->diffForHumans($endingDate,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);      
                            return trans('research.researchStarted', ['name' => trans('research.'.$technology->slug.'.name', [], $this->player->lang), 'level' => $wantedLvl, 'time' => $buildingTime], $this->player->lang);
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
                                $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }

                            if(!$hasRequirements)
                                return trans('research.notYetDiscovered', [], $this->player->lang);

                            $wantedLevel = 1;
                            $currentLvl = $this->player->hasTechnology($technology);
                            if($currentLvl)
                                $wantedLevel += $currentLvl;
                
                            if(count($this->args) == 2 && (int)$this->args[1] >= 1 && $this->args[1] < 65)
                            {
                                $wantedLvl = (int)$this->args[1];
                                $currentLvl = $wantedLvl-1;
                            }

                            $buildingPrice = "";
                            $buildingPrices = $technology->getPrice($wantedLevel);
                            foreach (config('stargate.resources') as $resource)
                            {
                                if($technology->$resource > 0)
                                {
                                    $buildingPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($buildingPrices[$resource]))."\n";
                                }
                            }
                
                            $buildingTime = $technology->getTime($wantedLevel);
                            
                            /** Application des bonus */
                            $buildingTime *= $this->player->activeColony->getResearchBonus();
                
                            $now = Carbon::now();
                            $buildingEnd = $now->copy()->addSeconds($buildingTime);
                            $buildingTime = $now->diffForHumans($buildingEnd,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);                      
                            $displayedLvl = 0;
                            if($currentLvl)
                                $displayedLvl = $currentLvl;

                            $bonusString = "";
                            if(!is_null($technology->energy_bonus))
                            {
                                $bonus = $technology->energy_bonus*100-100;
                                $bonusString .= "+{$bonus}% ".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang)." ".trans('generic.produced', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->building_bonus))
                            {
                                $bonus = 100-$technology->building_bonus*100;
                                $bonusString .= "-{$bonus}% ".config('stargate.emotes.productionBuilding')." ".trans('generic.buildingTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->technology_bonus))
                            {
                                $bonus = 100-$technology->technology_bonus*100;
                                $bonusString .= "-{$bonus}% ".config('stargate.emotes.research')." ".trans('generic.researchTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->crafting_bonus))
                            {
                                $bonus = 100-($technology->crafting_bonus*100);
                                $bonusString .= "-{$bonus}% ".config('stargate.emotes.productionBuilding')." ".trans('generic.craftingTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->defence_bonus))
                            {
                                $bonus = 100-($technology->defence_bonus*100);
                                $bonusString .= "-{$bonus}% ".trans('generic.defenceTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->ship_bonus))
                            {
                                $bonus = 100-($technology->ship_bonus*100);
                                $bonusString .= "-{$bonus}% ".trans('generic.shipTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->ship_consumption_bonus))
                            {
                                $bonus = 100-($technology->ship_consumption_bonus*100);
                                $bonusString .= "-{$bonus}% ".trans('generic.shipConsumption', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->ship_speed_bonus))
                            {
                                $bonus = 100-($technology->ship_speed_bonus*100);
                                $bonusString .= "+{$bonus}% ".trans('generic.shipSpeed', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->fire_power_bonus))
                            {
                                $bonus = 100-($technology->fire_power_bonus*100);
                                $bonusString .= "+{$bonus}% ".trans('generic.firePower', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->hull_bonus))
                            {
                                $bonus = 100-($technology->hull_bonus*100);
                                $bonusString .= "+{$bonus}% ".trans('generic.hull', [], $this->player->lang)."\n";
                            }
                            if(!is_null($technology->shield_bonus))
                            {
                                $bonus = 100-($technology->shield_bonus*100);
                                $bonusString .= "+{$bonus}% ".trans('generic.shield', [], $this->player->lang)."\n";
                            }

                            if(empty($bonusString))
                                $bonusString = "/";

                            $embed = [
                                'author' => [
                                    'name' => $this->player->user_name,
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                ],
                                "title" => 'Lvl '.$displayedLvl.' - '.trans('research.'.$technology->slug.'.name', [], $this->player->lang),
                                "description" => trans('research.howTo', ['id' => $technology->id, 'slug' => $technology->slug, 'description' => trans('research.'.$technology->slug.'.description', [], $this->player->lang)], $this->player->lang),
                                'fields' => [
                                    [
                                        'name' => trans('generic.info', [], $this->player->lang),
                                        'value' => "ID: ".$technology->id."\n"."Slug: `".$technology->slug."`",
                                        'inline' => true
                                    ],
                                    [
                                        'name' => trans('generic.bonusPerLvl', [], $this->player->lang),
                                        'value' => $bonusString,
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
                        return trans('research.unknownTechnology', [], $this->player->lang);
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
        $displayList = $this->researchList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('research.technologyList', [], $this->player->lang),
            "description" => trans('research.genericHowTo', [], $this->player->lang),
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
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
                    $buildingPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($buildingPrices[$resource]));
                }
            }

            $buildingTime = $technology->getTime($wantedLevel);
            
            /** Application des bonus */
            $buildingTime *= $this->player->activeColony->getResearchBonus();

            $now = Carbon::now();
            $buildingEnd = $now->copy()->addSeconds($buildingTime);
            $buildingTime = $now->diffForHumans($buildingEnd,[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);   

            $displayedLvl = 0;
            if($currentLvl)
                $displayedLvl = $currentLvl;
                
            $hasRequirements = true;
            foreach($technology->requiredTechnologies as $requiredTechnology)
            {
                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                    $hasRequirements = false;
            }
            foreach($technology->requiredBuildings as $requiredBuilding)
            {
                $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                    $hasRequirements = false;
            }

            if($hasRequirements == true)
            {
                $embed['fields'][] = array(
                    'name' => $technology->id.' - '.trans('research.'.$technology->slug.'.name', [], $this->player->lang).' - LVL '.$displayedLvl,
                    'value' => "\nSlug: `".$technology->slug."`\n - ".trans('generic.duration', [], $this->player->lang).": ".$buildingTime."\n".trans('generic.price', [], $this->player->lang).": ".$buildingPrice,
                    'inline' => true
                );
            }
            else
            {
                $embed['fields'][] = array(
                    'name' => trans('research.hiddenTechnology', [], $this->player->lang),
                    'value' => trans('research.unDiscovered', [], $this->player->lang),
                    'inline' => true
                );
            }
        }
        return $embed;
    }
}
