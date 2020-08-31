<?php

namespace App\Commands;

use App\Unit;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Str;

class Craft extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $craftList;
    public $craftQueue;
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
                    echo PHP_EOL.'Execute Craft';
                    $this->craftList = Unit::all();      

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->craftList->count()/5);
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
                    echo PHP_EOL.'Execute Craft Queue';
                    if($this->player->activeColony->craftQueues->count() == 0)
                        return trans('craft.emptyQueue', [], $this->player->lang);
                    $this->craftQueue = $this->player->activeColony->craftQueues;
    
                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->craftQueue->count()/5);
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
                    $unit = Unit::where('id', (int)$this->args[0])->orWhere('slug', 'LIKE', $this->args[0].'%')->first();
                    if(!is_null($unit))
                    {
                        //Requirement
                        $hasRequirements = true;
                        foreach($unit->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvl = $this->player->hasTechnology($requiredTechnology);
                            if(!($currentLvl && $currentLvl >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;
                        }
                        foreach($unit->requiredBuildings as $requiredBuilding)
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
                        $unitPrices = $unit->getPrice($qty);

                        $missingResString = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($unit->$resource > 0 && $unitPrices[$resource] > $this->player->activeColony->$resource)
                            {
                                $hasEnough = false;
                                $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($unitPrices[$resource]-$this->player->activeColony->$resource));
                            }
                        }
                        if(!$hasEnough)
                            return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                        if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                            return trans('generic.busyBuilding', [], $this->player->lang);

                        $now = Carbon::now();
                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startCrafting($unit,$qty));
                        $buildingTime = $now->diffForHumans($endingDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('craft.buildingStarted', ['name' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang), 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);
                    
                    }
                    else
                        return trans('craft.unknownCraft', [], $this->player->lang);
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
            $displayList = $this->craftQueue->skip(5*($this->page -1))->take(5);
            
            $craftQueueString = "";
            foreach($displayList as $queuedCraft)
            {
                $now = Carbon::now();
                $craftTime = $now->diffForHumans($queuedCraft->pivot->craft_end,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);      

                $craftQueueString .= "1x ".trans('craft.'.$queuedCraft->slug.'.name', [], $this->player->lang)." - ".$craftTime."\n"; 
            }

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('craft.craftQueue', [], $this->player->lang),
                "description" => $craftQueueString,
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

            $displayList = $this->craftList->skip(5*($this->page -1))->take(5);
            
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('craft.craftList', [], $this->player->lang),
                "description" => trans('craft.howTo', [], $this->player->lang),
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
                    'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
                ),
            ];

            foreach($displayList as $unit)
            {
                $unitPrice = "";
                $unitPrices = $unit->getPrice(1);
                foreach (config('stargate.resources') as $resource)
                {
                    if($unit->$resource > 0)
                    {
                        if(!empty($unitPrice))
                            $unitPrice .= " ";
                        $unitPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($unitPrices[$resource]));
                    }
                }
                $unitTime = $unit->base_time;

                /** Application des bonus */
                $unitTime *= $this->player->activeColony->getCraftingBonus();

                $now = Carbon::now();
                $unitEnd = $now->copy()->addSeconds($unitTime);
                $unitTime = $now->diffForHumans($unitEnd,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);      

                $hasRequirements = true;
                foreach($unit->requiredTechnologies as $requiredTechnology)
                {
                    $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                    if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                        $hasRequirements = false;
                }
                foreach($unit->requiredBuildings as $requiredBuilding)
                {
                    $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                    if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                        $hasRequirements = false;
                }
                $capacityString = "";
                if(!is_null($unit->capacity) && $unit->capacity > 0)
                    $capacityString = trans('craft.capacity', ['capacity' => number_format($unit->capacity)], $this->player->lang)."\n";
                if($hasRequirements == true)
                {
                    $embed['fields'][] = array(
                        'name' => $unit->id.' - '.trans('craft.'.$unit->slug.'.name', [], $this->player->lang),
                        'value' => trans('craft.'.$unit->slug.'.description', [], $this->player->lang)."\nSlug: `".$unit->slug."`\n - ".trans('generic.duration', [], $this->player->lang).": ".$unitTime."\n".trans('generic.price', [], $this->player->lang).": ".$unitPrice."\n".$capacityString,
                        'inline' => true
                    );
                }
                else
                {
                    $embed['fields'][] = array(
                        'name' => $unit->id.' - '.trans('craft.'.$unit->slug.'.name', [], $this->player->lang),
                        'value' => "\nSlug: `".$unit->slug."\n".$capacityString.trans('craft.unDiscovered', [], $this->player->lang),
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
