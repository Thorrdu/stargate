<?php

namespace App\Commands;

use App\Defence;
use App\Technology;
use App\Building;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;
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
    public $closed;

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

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->defenceList->count()/5);
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
    
                        $filter = function($messageReaction){
                            if($messageReaction->user_id != $this->message->author->id || $this->closed == true)
                                return false;
                            
                            if($messageReaction->user_id == $this->message->author->id)
                            {
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                        $this->closed = true;
                                    }
                                    elseif($messageReaction->emoji->name == '⏪')
                                    {
                                        $this->page = 1;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
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
                                    elseif($messageReaction->emoji->name == '⏩')
                                    {
                                        $this->page = $this->maxPage;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ID, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                catch(\Exception $e)
                                {
                                    echo $e->getMessage();
                                }
                                return true;
                            }
                            else
                                return false;
                        };
                        $this->paginatorMessage->createReactionCollector($filter);
                    });
                }
                elseif(Str::startsWith('queue', $this->args[0]))
                {
                    echo PHP_EOL.'Execute Defence Queue';
                    if($this->player->activeColony->defenceQueues->count() == 0)
                        return trans('defence.emptyQueue', [], $this->player->lang);
                    $this->defenceQueue = $this->player->activeColony->defenceQueues;
    
                    $this->closed = false;
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
    
                        $filter = function($messageReaction){
                            if($messageReaction->user_id != $this->message->author->id || $this->closed == true)
                                return false;
                            
                            if($messageReaction->user_id == $this->message->author->id)
                            {
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                        $this->closed = true;
                                    }
                                    elseif($messageReaction->emoji->name == '⏪')
                                    {
                                        $this->page = 1;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
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
                                    elseif($messageReaction->emoji->name == '⏩')
                                    {
                                        $this->page = $this->maxPage;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ID, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                }
                                catch(\Exception $e)
                                {
                                    echo $e->getMessage();
                                }
                                return true;
                            }
                            else
                                return false;
                        };
                        $this->paginatorMessage->createReactionCollector($filter);
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

                            if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 15)
                            return trans('generic.busyBuilding', [], $this->player->lang);

                        $now = Carbon::now();
                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startDefence($defence,$qty));
                        $buildingTime = $now->diffForHumans($endingDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('defence.buildingStarted', ['name' => trans('defence.'.$defence->slug.'.name', [], $this->player->lang), 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);
                    
                    }
                    else
                        return trans('defence.unknownDefence', [], $this->player->lang);
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
            foreach($displayList as $queuedDefence)
            {
                $now = Carbon::now();
                $defenceTime = $now->diffForHumans($queuedDefence->pivot->defence_end,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);      

                $defenceQueueString .= "1x ".trans('defence.'.$queuedDefence->slug.'.name', [], $this->player->lang)." - ".$defenceTime."\n"; 
            }

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('defence.defenceQueue', [], $this->player->lang),
                "description" => $defenceQueueString,
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
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
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('defence.defenceList', [], $this->player->lang),
                "description" => trans('defence.howTo', [], $this->player->lang),
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
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
                    $armamentLvl = $this->player->hasTechnology($armamentTec);
                    if($armamentLvl)
                        $firePower *= pow(1.1,$armamentLvl);
                    $firePowerString = trans('defence.firePower', ['firepower' => number_format($firePower)], $this->player->lang);
                    
                    $hullString = trans('defence.hull', ['hull' => number_format($defence->hull)], $this->player->lang);
                    $embed['fields'][] = array(
                        'name' => $defence->id.' - '.trans('defence.'.$defence->slug.'.name', [], $this->player->lang),
                        'value' => trans('defence.'.$defence->slug.'.description', [], $this->player->lang)."\nSlug: `".$defence->slug."`\n - ".
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
