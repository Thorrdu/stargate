<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\myDiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Str;

class Build extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $buildingList;
    public $buildingListType;
    public $closed;

    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            $this->player->activeColony->checkColony();
            $this->player->refresh();

            try{
                if(empty($this->args) || Str::startsWith('list', $this->args[0]))
                {
                    echo PHP_EOL.'Execute Build';
                    if($this->player->activeColony->id == $this->player->colonies[0]->id)
                        $this->buildingList = Building::Where('type', 'Energy')->orWhere('type','Production')->orderBy('Type','ASC')->get();
                    else
                        $this->buildingList = Building::Where('type', 'Energy')->orWhere([['type','Production'],['id','!=',19]])->orderBy('Type','ASC')->get();//Pas l usine Asuran
                    $this->buildingListType = trans('generic.productionBuildings',[],$this->player->lang);

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->buildingList->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;

                        $this->paginatorMessage->react('◀️')->then(function(){
                            $this->paginatorMessage->react('▶️')->then(function(){
                                $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.productionBuilding')))->then(function(){
                                    $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.researchBuilding')))->then(function(){
                                        $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.military')))->then(function(){
                                            $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.storage')))->then(function(){
                                                $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                            });
                                        });
                                    });
                                });
                            });
                        });

                        $filter = function($messageReaction){
                            if($messageReaction->user_id != $this->player->user_id || $this->closed == true)
                                return false;

                            if($messageReaction->user_id == $this->player->user_id)
                            {
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                        $this->closed = true;
                                        return;
                                    }
                                    elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                    {
                                        $this->page--;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                    {
                                        $this->page++;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == 'productionBuilding'
                                        || $messageReaction->emoji->name == 'storage'
                                        || $messageReaction->emoji->name == 'researchBuilding'
                                        || $messageReaction->emoji->name == 'military')
                                    {
                                        switch($messageReaction->emoji->name)
                                        {
                                            case 'productionBuilding':
                                                if($this->player->activeColony->id == $this->player->colonies[0]->id)
                                                    $this->buildingList = Building::Where('type', 'Energy')->orWhere('type','Production')->orderBy('Type','ASC')->get();
                                                else
                                                    $this->buildingList = Building::Where('type', 'Energy')->orWhere([['type','Production'],['id','!=',19]])->orderBy('Type','ASC')->get();//Pas l usine Asuran
                                                $this->buildingListType = trans('generic.productionBuildings',[],$this->player->lang);
                                            break;
                                            case 'storage':
                                                $this->buildingList = Building::where('type','Storage')->get();
                                                $this->buildingListType = trans('generic.storageBuildings',[],$this->player->lang);
                                            break;
                                            case 'researchBuilding':
                                                $this->buildingList = Building::where('type','Science')->get();
                                                $this->buildingListType = trans('generic.scienceBuildings',[],$this->player->lang);
                                            break;
                                            case 'military':
                                                $this->buildingList = Building::where('type','Military')->get();
                                                $this->buildingListType = trans('generic.militaryBuildings',[],$this->player->lang);
                                            break;
                                        }
                                        $this->page = 1;
                                        $this->maxPage = ceil($this->buildingList->count()/5);
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ID, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                catch(\Exception $e)
                                {
                                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                }
                                return true;
                            }
                            else
                                return false;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['time' => config('stargate.maxCollectionTime')]);
                    });
                }
                elseif(Str::startsWith('cancel', $this->args[0]))
                {
                    try
                    {
                        //if aucune construction en cours, return
                        if($this->player->activeColony->active_building_remove)
                            return trans('building.cantCancelRemove',[],$this->player->lang);
                        if(is_null($this->player->activeColony->active_building_end))
                        {
                            return trans('building.noActiveBuilding',[],$this->player->lang);
                        }
                        else
                        {
                            //CONFIRM
                            $cancelConfirm = trans('building.cancelBuildConfirm', [], $this->player->lang);
                            $embed = [
                                'author' => [
                                    'name' => $this->player->user_name,
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                ],
                                "title" => "Build",
                                "description" => $cancelConfirm,
                                'fields' => [
                                ],
                                'footer' => array(
                                    'text'  => 'Stargate',
                                ),
                            ];
                            $newEmbed = $this->discord->factory(Embed::class,$embed);

                            $this->maxTime = time()+180;
                            $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent){

                                $this->closed = false;
                                $this->paginatorMessage = $messageSent;
                                $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                    $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                    });
                                });

                                $filter = function($messageReaction){
                                    return $messageReaction->user_id == $this->player->user_id;
                                };
                                $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector){
                                    $messageReaction = $collector->first();
                                    try{
                                        if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                        {
                                            $this->player->load('activeColony');
                                            if($this->player->activeColony->active_building_remove)
                                            {
                                                $newEmbed = $this->discord->factory(Embed::class,[
                                                    'title' => trans('generic.cancelled', [], $this->player->lang),
                                                    'description' => trans('building.cantCancelRemove',[],$this->player->lang)
                                                    ]);
                                                $messageReaction->message->addEmbed($newEmbed);
                                            }
                                            if(is_null($this->player->activeColony->active_building_end))
                                            {
                                                return ;
                                                $newEmbed = $this->discord->factory(Embed::class,[
                                                    'title' => trans('generic.cancelled', [], $this->player->lang),
                                                    'description' => trans('building.noActiveBuilding',[],$this->player->lang)
                                                    ]);
                                                $messageReaction->message->addEmbed($newEmbed);
                                            }
                                            else
                                            {
                                                $cancelledBuilding = $this->player->activeColony->activeBuilding;

                                                $wantedLvl = 1;
                                                $currentLvl = $this->player->activeColony->hasBuilding($cancelledBuilding);
                                                if($currentLvl)
                                                    $wantedLvl += $currentLvl;

                                                $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);

                                                $buildingPrices = $cancelledBuilding->getPrice($wantedLvl, $coef);
                                                foreach(config('stargate.resources') as $resource)
                                                {
                                                    if(isset($buildingPrices[$resource]) && $buildingPrices[$resource] > 0)
                                                    {
                                                        $newResource = $this->player->activeColony->$resource + ceil($buildingPrices[$resource]*0.75);
                                                        if(($this->player->activeColony->{'storage_'.$resource}*1.25) <= $newResource)
                                                            $newResource = $this->player->activeColony->{'storage_'.$resource}*1.25;
                                                        $this->player->activeColony->$resource = $newResource;
                                                    }
                                                }
                                                $bufferedId = $this->player->activeColony->active_building_id;
                                                $this->player->activeColony->active_building_id = null;
                                                $this->player->activeColony->active_building_end = null;
                                                $this->player->activeColony->save();

                                                if(!is_null($this->player->premium_expiration))
                                                {

                                                    foreach($this->player->activeColony->buildingQueue as $buildinQueued)
                                                    {
                                                        if($buildinQueued->id == $bufferedId)
                                                        {
                                                            //echo PHP_EOL.$buildinQueued->pivot->level;
                                                            /*$buildinQueued->pivot->level--;
                                                            $buildinQueued->save();*/
                                                            //$this->player->activeColony->buildingQueue()->where('buildings.id', $buildinQueued->id)->wherePivot('level', $buildinQueued->pivot->level)->detach();
                                                        }
                                                    }
                                                    $this->player->activeColony->checkBuildingQueue();
                                                }

                                                $embed = [
                                                    'author' => [
                                                        'name' => $this->player->user_name,
                                                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                                    ],
                                                    "title" => "Build",
                                                    "description" => trans('building.buildingCanceled',[],$this->player->lang),
                                                    'fields' => [
                                                    ],
                                                    'footer' => array(
                                                        'text'  => 'Stargate',
                                                    ),
                                                ];
                                                $newEmbed = $this->discord->factory(Embed::class,$embed);
                                                $messageReaction->message->addEmbed($newEmbed);
                                            }
                                        }
                                        elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                'title' => trans('generic.cancelled', [], $this->player->lang)
                                                ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                });
                            });
                            return;

                        }
                    }
                    catch(\Exception $e)
                    {
                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                    }
                }
                elseif(Str::startsWith('queue', $this->args[0]))
                {
                    if(is_null($this->player->premium_expiration))
                        return trans('premium.restrictedCommand', [], $this->player->lang);

                    echo PHP_EOL.'Execute Building Queue';
                    if($this->player->activeColony->buildingQueue->count() == 0)
                        return trans('building.emptyQueue', [], $this->player->lang);

                    if(isset($this->args[1]) && Str::startsWith('clear', $this->args[1]))
                    {
                        $this->player->activeColony->buildingQueue()->detach();
                        return trans('building.clearedQueue', [], $this->player->lang);
                    }

                    $buildingQueueString = "";
                    $queueIndex = 1;
                    $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->active_building_end);

                    foreach($this->player->activeColony->buildingQueue as $queuedBuilding)
                    {
                        $buildingQueueString .= $queueIndex.'. Lvl '.$queuedBuilding->pivot->level.' - '.trans('building.'.$queuedBuilding->slug.'.name', [], $this->player->lang)."\n";

                        $buildingTime = $queuedBuilding->getTime($queuedBuilding->pivot->level);
                        /** Application des bonus */
                        $buildingTime *= $this->player->activeColony->getBuildingBonus($queuedBuilding->id);
                        $buildingEnd->addSeconds($buildingTime);

                        $queueIndex++;
                    }

                    $now = Carbon::now();
                    $totalBuildingTime = $now->diffForHumans($buildingEnd,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                       // 'thumbnail' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/planets/'.$this->player->activeColony->image],
                        "title" => trans('building.queueList',[],$this->player->lang),
                        "description" => $buildingQueueString."\n".trans('building.estimatedQueuedTotal', ['totalTime' => $totalBuildingTime], $this->player->lang)."\n".trans('building.howToClearQueue', [], $this->player->lang),
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    $this->closed = false;
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $embed)->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;

                        $this->paginatorMessage->react(config('stargate.emotes.cancel'));

                        $filter = function($messageReaction){
                            if($messageReaction->user_id != $this->player->user_id || $this->closed == true)
                                return false;

                            if($messageReaction->user_id == $this->player->user_id)
                            {
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closed', [], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                        $this->closed = true;
                                    }
                                }
                                catch(\Exception $e)
                                {
                                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                }
                                return true;
                            }
                            else
                                return false;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['time' => config('stargate.maxCollectionTime')]);
                    });
                    return;

                }
                elseif(!empty(trim($this->args[0])))
                {
                    if(is_numeric($this->args[0]) && $this->args[0] > 0)
                        $building = Building::where('id', (int)$this->args[0])->first();
                    else
                        $building = Building::where('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($building))
                    {
                        if($building->id == 19 && $this->player->activeColony->id != $this->player->colonies[0]->id)
                            return trans('building.asuranRestriction', [], $this->player->lang);

                        if(count($this->args) == 2 && (Str::startsWith('confirm', $this->args[1]) || Str::startsWith('remove', $this->args[1])))
                        {
                            $removal = false;
                            if(Str::startsWith('remove', $this->args[1]))
                                $removal = true;

                            $wantedLvl = 1;
                            $currentLvl = $this->player->activeColony->hasBuilding($building);
                            if($currentLvl && !$removal)
                                $wantedLvl += $currentLvl;
                            elseif($currentLvl && $removal)
                                $wantedLvl = $currentLvl;

                            //if construction en cours, return
                            if(!is_null($this->player->activeColony->active_building_end) && !$removal)
                            {
                                if(!is_null($this->player->premium_expiration))
                                {
                                    if($this->player->activeColony->buildingQueue->count() >= 5)
                                        return trans('building.queueIsFull', [], $this->player->lang);

                                    $levelToQueue = $wantedLvl;
                                    if($this->player->activeColony->active_building_id == $building->id)
                                        $levelToQueue++;
                                    foreach($this->player->activeColony->buildingQueue as $queuedBuilding)
                                        if($queuedBuilding->id == $building->id)
                                            $levelToQueue++;

                                    $this->player->activeColony->buildingQueue()->attach([$building->id => ['level' => $levelToQueue]]);
                                    return trans('building.buildingQueueAdded', ['buildingName' => 'Lvl '.$levelToQueue.' - '.trans('building.'.$building->slug.'.name', [], $this->player->lang)], $this->player->lang);
                                }
                                else
                                {
                                    $wantedLvl = 1;
                                    $currentLvl = $this->player->activeColony->hasBuilding($this->player->activeColony->activeBuilding);
                                    if($this->player->activeColony->active_building_remove)
                                        $wantedLvl = $currentLvl-1;
                                    elseif($currentLvl)
                                        $wantedLvl += $currentLvl;

                                    $now = Carbon::now();
                                    $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->active_building_end);
                                    $buildingTime = $now->diffForHumans($buildingEnd,[
                                        'parts' => 3,
                                        'short' => true, // short syntax as per current locale
                                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                    ]);
                                    //:level :name will be done in :time
                                    return trans('building.alreadyBuilding', ['level' => $wantedLvl, 'name' => trans('building.'.$this->player->activeColony->activeBuilding->slug.'.name', [], $this->player->lang), 'time' => $buildingTime], $this->player->lang);
                                }
                            }

                            if(!$removal)
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
                                    $currentLvl = $this->player->activeColony->hasBuilding($requiredBuilding);
                                    if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                                        $hasRequirements = false;
                                }
                                if(!$hasRequirements)
                                    return trans('generic.missingRequirements', [], $this->player->lang);

                                if(!is_null($building->level_max) && $wantedLvl > $building->level_max)
                                {
                                    return trans('building.buildingMaxed', [], $this->player->lang);
                                }

                                if(($this->player->activeColony->space_max - $this->player->activeColony->space_used) <= 0 && $building->id != 20)
                                    return trans('building.missingSpace', [], $this->player->lang);

                                $hasEnough = true;
                                $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);

                                $buildingPrices = $building->getPrice($wantedLvl, $coef);
                                $missingResString = "";
                                foreach (config('stargate.resources') as $resource)
                                {
                                    if($building->$resource > 0 && $buildingPrices[$resource] > $this->player->activeColony->$resource)
                                    {
                                        $hasEnough = false;
                                        $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($buildingPrices[$resource]-$this->player->activeColony->$resource));
                                    }
                                }
                                if(!$hasEnough)
                                    return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                                if($building->energy_base > 0 && $building->id != 10 /*Reacteur au Naqahdah*/)
                                {
                                    $energyPrice = $building->getEnergy($wantedLvl);
                                    if($wantedLvl > 1)
                                        $energyPrice -= $building->getEnergy($wantedLvl-1);
                                    $energyLeft = ($this->player->activeColony->energy_max - $this->player->activeColony->energy_used);
                                    $missingEnergy = $energyPrice - $energyLeft;
                                    if($missingEnergy > 0)
                                        return trans('building.notEnoughEnergy', ['missingEnergy' => $missingEnergy], $this->player->lang);
                                }
                            }

                            if( !is_null($this->player->active_technology_id) && $building->id == 7 && $this->player->activeColony->id == $this->player->active_technology_colony_id)
                                return trans('generic.busyBuilding', [], $this->player->lang);

                            if( $this->player->activeColony->defenceQueues->count() > 0 && $building->id == 15 )
                                return trans('generic.busyBuilding', [], $this->player->lang);

                            if( $building->id == 9 && ( $this->player->activeColony->craftQueues->count() > 0 || $this->player->activeColony->shipQueues->count() > 0 || $this->player->activeColony->reyclingQueue->count() > 0 ))
                                return trans('generic.busyBuilding', [], $this->player->lang);

                            $now = Carbon::now();
                            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startBuilding($building,$wantedLvl,$removal));
                            $buildingTime = $now->diffForHumans($endingDate,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);

                            if($removal)
                                return trans('building.buildingRemovalStarted', ['name' => trans('building.'.$building->slug.'.name', [], $this->player->lang), 'time' => $buildingTime], $this->player->lang);
                            else
                                return trans('building.buildingStarted', ['name' => trans('building.'.$building->slug.'.name', [], $this->player->lang), 'level' => $wantedLvl, 'time' => $buildingTime], $this->player->lang);
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
                                $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }
                            if(!$hasRequirements)
                            {
                                return trans('building.notYetDiscovered', [], $this->player->lang);
                            }

                            $wantedLvl = 1;
                            $currentLvl = $this->player->activeColony->hasBuilding($building);
                            if($currentLvl)
                                $wantedLvl += $currentLvl;

                            if(count($this->args) == 2 && (int)$this->args[1] >= 1 && $this->args[1] < 65)
                            {
                                $wantedLvl = (int)$this->args[1];
                                $currentLvl = $wantedLvl-1;
                            }

                            if(!is_null($building->level_max) && $wantedLvl > $building->level_max)
                            {
                                $buildingPrice = "Maxed";
                                $buildingTime = "Maxed";
                            }
                            else
                            {
                                $buildingPrice = "";

                                $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);

                                $buildingPrices = $building->getPrice($wantedLvl, $coef);
                                foreach (config('stargate.resources') as $resource)
                                {
                                    if($building->$resource > 0)
                                    {
                                        $buildingPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($buildingPrices[$resource]));

                                        if($this->player->activeColony->$resource >= ceil($buildingPrices[$resource]))
                                            $buildingPrice .= ' '.config('stargate.emotes.confirm');
                                        else
                                            $buildingPrice .= ' '.config('stargate.emotes.cancel');
                                    }
                                }
                                if($building->energy_base > 0 && $building->id != 10 /*Reacteur au Naqahdah*/)
                                {
                                    $energyRequired = $building->getEnergy($wantedLvl);
                                    if($wantedLvl > 1)
                                        $energyRequired -= $building->getEnergy($wantedLvl-1);
                                    $buildingPrice .= "\n".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang)." ".number_format(round($energyRequired));

                                    $energyLeft = ($this->player->activeColony->energy_max - $this->player->activeColony->energy_used);
                                    if($energyLeft >= $energyRequired)
                                        $buildingPrice .= ' '.config('stargate.emotes.confirm');
                                    else
                                        $buildingPrice .= ' '.config('stargate.emotes.cancel');
                                }

                                $buildingTime = $building->getTime($wantedLvl);
                                /** Application des bonus */
                                $buildingTime *= $this->player->activeColony->getBuildingBonus($building->id);
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
                            if(!is_null($building->defence_bonus))
                            {
                                $bonus = 100-($building->defence_bonus*100);
                                $bonusString .= "-{$bonus}% ".trans('generic.defenceTime', [], $this->player->lang)."\n";
                            }
                            if(!is_null($building->ship_bonus))
                            {
                                $bonus = 100-($building->ship_bonus*100);
                                $bonusString .= "-{$bonus}% ".trans('generic.shipTime', [], $this->player->lang)."\n";
                            }

                            $productionString = $consoString = "";
                            if(!is_null($building->production_base))
                            {
                                if($building->type == "Energy")
                                {
                                    if($currentLvl)
                                        $productionString .= "Lvl ".$currentLvl." - ".config('stargate.emotes.energy')." ".number_format($building->getProductionEnergy($currentLvl))."\n";
                                    $productionString .= "Lvl ".($currentLvl+1)." - ".config('stargate.emotes.energy')." ".number_format($building->getProductionEnergy($currentLvl+1));
                                }
                                else
                                {
                                    if($building->slug == 'asuranfactory')
                                    {
                                        if($currentLvl)
                                            $productionString .= "Lvl ".$currentLvl." - ".config('stargate.emotes.e2pz')." ".number_format(config('stargate.base_prod.e2pz')+$building->getProductionE2PZ($currentLvl))."\n";
                                        $productionString .= "Lvl ".($currentLvl+1)." - ".config('stargate.emotes.e2pz')." ".number_format(config('stargate.base_prod.e2pz')+$building->getProductionE2PZ($currentLvl+1));
                                    }
                                    else
                                    {
                                        if($currentLvl)
                                            $productionString .= "Lvl ".$currentLvl." - ".config('stargate.emotes.'.$building->production_type)." ".number_format($building->getProduction($currentLvl))."\n";
                                        $productionString .= "Lvl ".($currentLvl+1)." - ".config('stargate.emotes.'.$building->production_type)." ".number_format($building->getProduction($currentLvl+1));
                                    }
                                }
                            }

                            if($building->slug == 'naqahdahreactor')
                            {
                                if($currentLvl)
                                    $consoString .= "Lvl ".$currentLvl." - ".config('stargate.emotes.naqahdah')." ".number_format($building->getConsumption($currentLvl))."\n";
                                $consoString .= "Lvl ".($currentLvl+1)." - ".config('stargate.emotes.naqahdah')." ".number_format($building->getConsumption($currentLvl+1));
                            }
                            elseif(!is_null($building->energy_base))
                            {
                                if($currentLvl)
                                    $consoString .= "Lvl ".$currentLvl." - ".config('stargate.emotes.energy')." ".number_format($building->getEnergy($currentLvl))."\n";
                                $consoString .= "Lvl ".($currentLvl+1)." - ".config('stargate.emotes.energy')." ".number_format($building->getEnergy($currentLvl+1));
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
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                ],
                                "title" => 'Lvl '.$displayedLvl.' - '.trans('building.'.$building->slug.'.name', [], $this->player->lang),
                                "description" => trans('building.howTo', ['id' => $building->id, 'slug' => $building->slug, 'description' => trans('building.'.$building->slug.'.description', [], $this->player->lang)], $this->player->lang),
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

                            $newEmbed = $this->discord->factory(Embed::class,$embed);
                            $this->message->channel->sendMessage('', false, $newEmbed);
                        }
                    }
                    else
                        return trans('building.unknownBuilding', [], $this->player->lang);
                }
            }
            catch(\Exception $e)
            {
                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => $this->buildingListType,
            "description" => trans('building.genericHowTo', [], $this->player->lang),
            'fields' => [],
            'footer' => array(
                //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];
        /*
        $embed['description'] .= "\n".trans('generic.availableRessources', [], $this->player->lang);
        foreach (config('stargate.resources') as $resource)
        {
            $embed['description'] .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(floor($this->player->activeColony->$resource));
        }
        $embed['description'] .= "\n\n ";*/

        foreach($displayList as $building)
        {
            $wantedLvl = 1;
            $currentLvl = $this->player->activeColony->hasBuilding($building);
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

                $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);

                $buildingPrices = $building->getPrice($wantedLvl, $coef);
                foreach (config('stargate.resources') as $resource)
                {
                    if($building->$resource > 0)
                    {
                        $buildingPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($buildingPrices[$resource]));

                        if($this->player->activeColony->$resource >= ceil($buildingPrices[$resource]))
                            $buildingPrice .= ' '.config('stargate.emotes.confirm');
                        else
                            $buildingPrice .= ' '.config('stargate.emotes.cancel');
                    }
                }
                if($building->energy_base > 0 && $building->id != 10 /*Reacteur au Naqahdah*/)
                {
                    $energyRequired = $building->getEnergy($wantedLvl);
                    if($wantedLvl > 1)
                        $energyRequired -= $building->getEnergy($wantedLvl-1);
                    $buildingPrice .= "\n".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang)." ".number_format(round($energyRequired));

                    $energyLeft = ($this->player->activeColony->energy_max - $this->player->activeColony->energy_used);
                    if($energyLeft >= $energyRequired)
                        $buildingPrice .= ' '.config('stargate.emotes.confirm');
                    else
                        $buildingPrice .= ' '.config('stargate.emotes.cancel');
                }

                $buildingTime = $building->getTime($wantedLvl);

                /** Application des bonus */
                $buildingTime *= $this->player->activeColony->getBuildingBonus($building->id);

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
                $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                    $hasRequirements = false;
            }

            if($hasRequirements == true)
            {
                $embed['fields'][] = array(
                    'name' => $building->id.' - '.trans('building.'.$building->slug.'.name', [], $this->player->lang).' - Lvl '.$displayedLvl,
                    'value' => "\nSlug: `".$building->slug."`\n - ".trans('generic.duration', [], $this->player->lang).": ".$buildingTime."\n".trans('generic.price', [], $this->player->lang).": ".$buildingPrice,
                    'inline' => true
                );
            }
            else
            {
                $requirementString = '';
                foreach($building->requiredTechnologies as $requiredTechnology)
                {
                    $techLevel = $this->player->hasTechnology($requiredTechnology);
                    if(!$techLevel)
                        $techLevel = 0;

                    $requirementString .= trans('research.'.$requiredTechnology->slug.'.name', [], $this->player->lang)." Lvl ".$requiredTechnology->pivot->level." (".$techLevel;
                    if($techLevel >= $requiredTechnology->pivot->level)
                        $requirementString .= ' '.config('stargate.emotes.confirm').")\n";
                    else
                        $requirementString .= ' '.config('stargate.emotes.cancel').")\n";
                }
                foreach($building->requiredBuildings as $requiredBuilding)
                {
                    $buildLvl = $this->player->activeColony->hasBuilding($requiredBuilding);
                    if(!$buildLvl)
                        $buildLvl = 0;
                    $requirementString .= trans('building.'.$requiredBuilding->slug.'.name', [], $this->player->lang)." Lvl ".$requiredBuilding->pivot->level." (".$buildLvl;
                    if($buildLvl >= $requiredBuilding->pivot->level)
                        $requirementString .= ' '.config('stargate.emotes.confirm').")\n";
                    else
                        $requirementString .= ' '.config('stargate.emotes.cancel').")\n";
                }

                $embed['fields'][] = array(
                    'name' => $building->id.' - '.trans('building.'.$building->slug.'.name', [], $this->player->lang),
                    'value' => $requirementString,
                    'inline' => true
                );
            }
        }

        return $embed;
    }

}
