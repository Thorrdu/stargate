<?php

namespace App\Commands;

use App\Defence;
use App\Technology;
use App\Building;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class DefenceCommand extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $defenceList;
    public $defenceQueue;
    
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            $this->player->activeColony->checkColony();
            $this->player->refresh();

            try{

                if(empty($this->args) || Str::startsWith('list', $this->args[0]))
                {
                    echo PHP_EOL.'Execute Defence';
                    $this->defenceList = Defence::all();      

                    $this->page = 1;
                    $this->maxPage = ceil($this->defenceList->count()/5);
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
                elseif(Str::startsWith('queue', $this->args[0]))
                {
                    echo PHP_EOL.'Execute Defence Queue';
                    if($this->player->activeColony->defenceQueues->count() == 0)
                        return trans('defence.emptyQueue', [], $this->player->lang);
                    $this->defenceQueue = $this->player->activeColony->defenceQueues;
    
                    $this->page = 1;
                    $this->maxPage = ceil($this->defenceQueue->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getQueue())->then(function ($messageSent){
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
                            if($this->maxTime < time())
                            {
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->name), null);
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                            }
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->name), null);
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                }
                                elseif($messageReaction->emoji->name == '⏪')
                                {
                                    $this->page = 1;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getQueue());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                {
                                    $this->page--;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getQueue());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                {
                                    $this->page++;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getQueue());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                elseif($messageReaction->emoji->name == '⏩')
                                {
                                    $this->page = $this->maxPage;
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getQueue());
                                    $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                            }
                        };
                        $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                    });
                }
                else
                {
                    $defence = Defence::where('id', (int)$this->args[0])->orWhere('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($defence))
                    {
                        //Requirement
                        $hasRequirements = true;
                        foreach($defence->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvl = $this->player->hasTechnology($requiredTechnology);
                            if(!($currentLvl && $currentLvl >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;
                        }
                        foreach($defence->requiredBuildings as $requiredBuilding)
                        {
                            $currentLvl = $this->player->activeColony->hasBuilding($requiredBuilding);
                            if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                                $hasRequirements = false;
                        }
                        if(!$hasRequirements)
                            return trans('generic.missingRequirements', [], $this->player->lang);
                        
                        if(count($this->args) >= 2 && (int)$this->args[1] > 0)
                            $qty = (int)$this->args[1];
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);

                        $hasEnough = true;
                        $defencePrices = $defence->getPrice($qty);

                        $missingResString = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($defence->$resource > 0 && $defencePrices[$resource] > $this->player->activeColony->$resource)
                            {
                                $hasEnough = false;
                                $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($defencePrices[$resource]-$this->player->activeColony->$resource));
                            }
                        }
                        if(!$hasEnough)
                            return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                        if(!is_null($this->player->active_technology_id) && $defence->id == 15)
                            return trans('generic.busyBuilding', [], $this->player->lang);

                        $now = Carbon::now();
                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startDefence($defence,$qty));
                        $buildingTime = $now->diffForHumans($endingDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('defence.buildingStarted', ['name' => config('defence.'.$defence->name.'.name', [], $this->player->lang), 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);
                    
                    }
                    else
                        return trans('defence.unknownCraft', [], $this->player->lang);
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

    public function getQueue()
    {
        try
        {
            $displayList = $this->defenceQueue->skip(5*($this->page -1))->take(5);
            
            $defenceQueueString = "";
            foreach($displayList as $queuedCraft)
            {
                $now = Carbon::now();
                $craftTime = $now->diffForHumans($queuedCraft->pivot->craft_end,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);      

                $defenceQueueString .= "1x ".$queuedCraft->name." - ".$craftTime."\n"; 
            }

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => trans('defence.defenceQueue', [], $this->player->lang),
                "description" => $defenceQueueString,
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                    'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
                ),
            ];

            return $embed;
        } 
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }

    public function getPage()
    {
        try{

            $displayList = $this->defenceList->skip(5*($this->page -1))->take(5);
            
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => trans('defence.defenceList', [], $this->player->lang),
                "description" => trans('defence.howTo', [], $this->player->lang),
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                    'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
                ),
            ];

            foreach($displayList as $defence)
            {
                $defencePrice = "";
                $defencePrices = $defence->getPrice(1);
                foreach (config('stargate.resources') as $resource)
                {
                    if($defence->$resource > 0)
                    {
                        if(!empty($defencePrice))
                            $defencePrice .= " ";
                        $defencePrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($defencePrices[$resource]));
                    }
                }
                $defenceTime = $defence->base_time;

                /** Application des bonus */
                $defenceTime *= $this->player->activeColony->getDefencebuildBonus();

                $now = Carbon::now();
                $defenceEnd = $now->copy()->addSeconds($defenceTime);
                $defenceTime = $now->diffForHumans($defenceEnd,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);      

                $hasRequirements = true;
                foreach($defence->requiredTechnologies as $requiredTechnology)
                {
                    $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                    if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                        $hasRequirements = false;
                }
                foreach($defence->requiredBuildings as $requiredBuilding)
                {
                    $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                    if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                        $hasRequirements = false;
                }                    
                if($hasRequirements == true)
                {
                    $firePower = $defence->fire_power;
                    $armamentTec = Technology::Where('slug', 'LIKE', 'armament')->first();
                    $armamentLvl = $this->$this->coordinateDestination->colony->player->hasTechnology($armamentTec);
                    if($armamentLvl)
                        $firePower *= pow(1.1,$armamentLvl);
                    $firePowerString = trans('defence.firePower', ['firepower' => number_format($firePower)], $this->player->lang);
                    
                    $hullString = trans('defence.capacity', ['capacity' => number_format($defence->capacity)], $this->player->lang);
                    $embed['fields'][] = array(
                        'name' => $defence->id.' - '.config('defence.'.$defence->name.'.name', [], $this->player->lang),
                        'value' => config('defence.'.$defence->slug.'.description', [], $this->player->lang)."\nSlug: `".$defence->slug."`\n - ".
                                   trans('generic.duration', [], $this->player->lang).": ".$defenceTime."\n".trans('generic.price', [], $this->player->lang).": ".
                                   $defencePrice."\n".$firePowerString."\n".$hullString,
                        'inline' => true
                    );
                }
                else
                {
                    $embed['fields'][] = array(
                        'name' => trans('defence.hidden', [], $this->player->lang),
                        'value' => trans('defence.unDiscovered', [], $this->player->lang),
                        'inline' => true
                    );
                }
            }

            return $embed;

            }
            
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }

}