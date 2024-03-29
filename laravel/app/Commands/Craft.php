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

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

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
                                    elseif($messageReaction->emoji->name == '⏪')
                                    {
                                        $this->page = 1;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                    {
                                        $this->page--;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                    {
                                        $this->page++;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '⏩')
                                    {
                                        $this->page = $this->maxPage;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
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
                        $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Craft']);
                        $unitPrices = $unit->getPrice($qty, $coef);
                        $missingResString = $crafPrice = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($unit->$resource > 0 && $unitPrices[$resource] > $this->player->activeColony->$resource)
                            {
                                $hasEnough = false;
                                $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($unitPrices[$resource]-$this->player->activeColony->$resource));
                            }
                            elseif($unit->$resource > 0)
                                $crafPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($unitPrices[$resource]));
                        }
                        if(!$hasEnough)
                            return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                        if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                            return trans('generic.busyBuilding', [], $this->player->lang);

                        $unitTime = $unit->base_time;
                        /** Application des bonus */
                        $unitTime *= $this->player->activeColony->getCraftingBonus();
                        $now = Carbon::now();
                        $unitEnd = $now->copy()->addSeconds($unitTime*$qty);
                        $unitTime = $now->diffForHumans($unitEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        //CONFIRM
                        $craftConfirm = trans('generic.genericBuildConfirmDesc', [
                            'qty' => $qty,
                            'stuffToBuild' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang),
                            'resources' => $crafPrice,
                            'time' => $unitTime,
                        ], $this->player->lang);
                        $embed = [
                            'author' => [
                                'name' => $this->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            "title" => trans('generic.genericBuildConfirmTitle', [], $this->player->lang),
                            "description" => $craftConfirm,
                            'fields' => [
                            ],
                            'footer' => array(
                                'text'  => 'Stargate',
                            ),
                        ];
                        $newEmbed = $this->discord->factory(Embed::class,$embed);

                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent) use($unit,$qty,$unitPrices){

                            $this->closed = false;
                            $this->paginatorMessage = $messageSent;
                            $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                });
                            });

                            $filter = function($messageReaction){
                                return $messageReaction->user_id == $this->player->user_id;
                            };
                            $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector) use($unit,$qty,$unitPrices){
                                $messageReaction = $collector->first();
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                    {
                                        $this->player->activeColony->refresh();

                                        $cancelReason = "";

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
                                            $cancelReason = trans('generic.missingRequirements', [], $this->player->lang);

                                        $hasEnough = true;
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
                                            $cancelReason = trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                                        if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                                            $cancelReason = trans('generic.busyBuilding', [], $this->player->lang);

                                        if(!empty($cancelReason))
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                'title' => trans('generic.cancelled', [], $this->player->lang),
                                                'description' => $cancelReason
                                                ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        else
                                        {
                                            $now = Carbon::now();
                                            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startCrafting($unit,$qty));
                                            $buildingTime = $now->diffForHumans($endingDate,[
                                                'parts' => 3,
                                                'short' => true, // short syntax as per current locale
                                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                            ]);
                                            $confirmMessage = trans('craft.buildingStarted', ['name' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang), 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);

                                            $embed = [
                                                'author' => [
                                                    'name' => $this->player->user_name,
                                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                                ],
                                                "title" => trans('generic.genericBuildConfirmTitle', [], $this->player->lang),
                                                "description" => $confirmMessage,
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
                    else
                        return trans('craft.unknownCraft', [], $this->player->lang);
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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

                $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Craft']);

                $unitPrices = $unit->getPrice(1, $coef);
                foreach (config('stargate.resources') as $resource)
                {
                    if($unit->$resource > 0)
                    {
                        $unitPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($unitPrices[$resource]));

                        if($this->player->activeColony->$resource >= ceil($unitPrices[$resource]))
                            $unitPrice .= ' '.config('stargate.emotes.confirm');
                        else
                            $unitPrice .= ' '.config('stargate.emotes.cancel');
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

                $speedBonus = $this->player->getShipSpeedBonus();

                $speedString = '';
                if(!is_null($unit->speed) && $unit->speed > 0)
                    $speedString = trans('shipyard.speed', ['speed' => round($unit->speed*$speedBonus,2)], $this->player->lang)."\n";

                if($hasRequirements == true)
                {
                    $ownedUnits = $this->player->activeColony->hasCraft($unit);
                    if(!$ownedUnits)
                        $ownedUnits = 0;

                    $embed['fields'][] = array(
                        'name' => $unit->id.' - '.trans('craft.'.$unit->slug.'.name', [], $this->player->lang).' ('.number_format($ownedUnits).')',
                        'value' => trans('craft.'.$unit->slug.'.description', [], $this->player->lang)."\nSlug: `".$unit->slug."`\n - ".trans('generic.duration', [], $this->player->lang).": ".$unitTime."\n".trans('generic.price', [], $this->player->lang).": ".$unitPrice."\n".$capacityString.$speedString,
                        'inline' => true
                    );
                }
                else
                {
                    $requirementString = '';
                    foreach($unit->requiredTechnologies as $requiredTechnology)
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
                    foreach($unit->requiredBuildings as $requiredBuilding)
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
                        'name' => $unit->id.' - '.trans('craft.'.$unit->slug.'.name', [], $this->player->lang),
                        'value' => "Slug: `".$unit->slug."`\n".$requirementString,
                        'inline' => true
                    );
                }
            }

            return $embed;

            }

        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

}
