<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Ship;
use App\Building;
use App\Technology;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Str;

class Shipyard extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $shipList;
    public $shipQueue;
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
                echo PHP_EOL.'Execute Shipyard';

                $shipyard = Building::find(9); // Shipyard
                $currentShipyardLvl = $this->player->activeColony->hasBuilding($shipyard);
                if(!$currentShipyardLvl)
                {
                    return trans('shipyard.notBuilt', [], $this->player->lang);
                }

                if(empty($this->args) || Str::startsWith('list', $this->args[0]))
                {
                    $this->shipList = $this->player->ships;

                    if(count($this->player->ships) == 0)
                    {
                        return trans('shipyard.emptyList', [], $this->player->lang);
                    }

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->shipList->count()/5);
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
                        $this->paginatorMessage->createReactionCollector($filter);
                    });
                }
                elseif(Str::startsWith('queue', $this->args[0]))
                {
                    echo PHP_EOL.'Execute Shipyard Queue';
                    if($this->player->activeColony->shipQueues->count() == 0)
                        return trans('shipyard.emptyQueue', [], $this->player->lang);
                    $this->shipQueue = $this->player->activeColony->shipQueues;

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->shipQueue->count()/5);
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
                        $this->paginatorMessage->createReactionCollector($filter);
                    });
                }
                else
                {
                    $ship = Ship::where([['player_id', $this->player->id],['slug', 'LIKE', $this->args[0].'%']])->first();
                    if(!is_null($ship))
                    {
                        //Requirement
                        $hasRequirements = true;

                        $blueprintTechnology = Technology::find(6);
                        $currentBlueprintLvl = $this->player->hasTechnology($blueprintTechnology);
                        if(!$currentBlueprintLvl || $currentBlueprintLvl < $ship->required_blueprint)
                            $hasRequirements = false;

                        $shipyard = Building::find(9); // Shipyard
                        $currentShipyardLvl = $this->player->activeColony->hasBuilding($shipyard);
                        if(!$currentShipyardLvl || $currentShipyardLvl < $ship->required_shipyard)
                            $hasRequirements = false;

                        if(!$hasRequirements)
                            return trans('generic.missingRequirements', [], $this->player->lang);

                        if(count($this->args) >= 2 && (int)$this->args[1] > 0)
                            $qty = (int)$this->args[1];
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);

                        $hasEnough = true;

                        $coef = 1;
                        $buildingPriceBonusList = $this->player->activeColony->artifacts->filter(function ($value){
                            return $value->bonus_category == 'Price' && $value->bonus_type == 'Ship';
                        });
                        foreach($buildingPriceBonusList as $buildingPriceBonus)
                        {
                            $coef *= $buildingPriceBonus->bonus_coef;
                        }

                        $shipPrices = $ship->getPrice($qty, $coef);

                        $missingResString = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($ship->$resource > 0 && $shipPrices[$resource] > $this->player->activeColony->$resource)
                            {
                                $hasEnough = false;
                                $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($shipPrices[$resource]-$this->player->activeColony->$resource));
                            }
                        }
                        if(!$hasEnough)
                            return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                        if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                        return trans('generic.busyBuilding', [], $this->player->lang);

                        $now = Carbon::now();
                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startShip($ship,$qty));
                        $buildingTime = $now->diffForHumans($endingDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('shipyard.buildingStarted', ['name' => $ship->name, 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);
                    }
                    else
                        return trans('shipyard.unknownShip', [], $this->player->lang);
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
        $displayList = $this->shipList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('shipyard.shipList', [], $this->player->lang),
            "description" => trans('shipyard.howTo', [], $this->player->lang),
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];
        $blueprintTechnology = Technology::find(6);
        $shipyard = Building::find(9); // Shipyard
        $armamentTec = Technology::Where('slug', 'LIKE', 'armament')->first();
        $armamentLvl = $this->player->hasTechnology($armamentTec);
        $shieldTec = Technology::Where('slug', 'LIKE', 'shield')->first();
        $shieldLvl = $this->player->hasTechnology($shieldTec);
        $hullTec = Technology::Where('slug', 'LIKE', 'hull')->first();
        $hullLvl = $this->player->hasTechnology($hullTec);

        foreach($displayList as $ship)
        {
            $shipPrice = "";

            $coef = 1;
            $buildingPriceBonusList = $this->player->activeColony->artifacts->filter(function ($value){
                return $value->bonus_category == 'Price' && $value->bonus_type == 'Ship';
            });
            foreach($buildingPriceBonusList as $buildingPriceBonus)
            {
                $coef *= $buildingPriceBonus->bonus_coef;
            }

            $shipPrices = $ship->getPrice(1, $coef);
            foreach (config('stargate.resources') as $resource)
            {
                if($ship->$resource > 0)
                {
                    if(!empty($shipPrice))
                        $shipPrice .= " ";
                    $shipPrice .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(round($shipPrices[$resource]));
                }
            }
            $shipTime = $ship->base_time;

            /** Application des bonus */
            $shipTime *= $this->player->activeColony->getShipbuildBonus();

            $now = Carbon::now();
            $shipEnd = $now->copy()->addSeconds($shipTime);
            $shipTime = $now->diffForHumans($shipEnd,[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);

            $hasRequirements = true;
            $requirementString = '';
            $currentBlueprintLvl = $this->player->hasTechnology($blueprintTechnology);
            $currentShipyardLvl = $this->player->activeColony->hasBuilding($shipyard);
            if((!$currentBlueprintLvl || $currentBlueprintLvl < $ship->required_blueprint) || (!$currentShipyardLvl || $currentShipyardLvl < $ship->required_shipyard))
            {
                $hasRequirements = false;
                if(!$currentBlueprintLvl)
                    $currentBlueprintLvl = 0;
                $requirementString .= trans('research.'.$blueprintTechnology->slug.'.name', [], $this->player->lang)." Lvl ".$ship->required_blueprint." ($currentBlueprintLvl)\n";
                if(!$currentShipyardLvl)
                    $currentShipyardLvl = 0;
                $requirementString .= trans('building.'.$shipyard->slug.'.name', [], $this->player->lang)." Lvl ".$ship->required_shipyard." ($currentShipyardLvl)\n";
            }

            if($hasRequirements)
            {
                $firePower = $ship->fire_power;
                if($armamentLvl)
                    $firePower *= pow(1.1,$armamentLvl);
                $shield = $ship->shield;
                if($shieldLvl)
                    $shield *= pow(1.1,$shieldLvl);
                $hull = $ship->hull;
                if($hullLvl)
                    $hull *= pow(1.1,$hullLvl);

                $speedBonus = $this->player->getShipSpeedBonus();
                if(!$speedBonus)
                    $speedBonus = 1;
                else
                    $speedBonus = 1+$speedBonus;

                $firePowerString = trans('shipyard.firePower', ['firepower' => config('stargate.emotes.armament').' '.number_format($firePower)], $this->player->lang);
                $shieldString = trans('shipyard.shield', ['shield' => config('stargate.emotes.shield').' '.number_format($shield)], $this->player->lang);
                $hullString = trans('shipyard.hull', ['hull' => config('stargate.emotes.hull').' '.number_format($hull)], $this->player->lang);
                $capacityString = trans('shipyard.capacity', ['capacity' => config('stargate.emotes.freight').' '.number_format($ship->capacity)], $this->player->lang);
                $crewString = trans('shipyard.crew', ['crew' => config('stargate.emotes.military').' '.number_format($ship->crew)], $this->player->lang);
                $speedString = trans('shipyard.speed', ['speed' => config('stargate.emotes.speed').' '.number_format($ship->speed*$speedBonus,2)], $this->player->lang);

                $embed['fields'][] = array(
                    'name' => $ship->name,
                    'value' => "\nSlug: `".$ship->slug."`\n - ".
                               trans('generic.duration', [], $this->player->lang).": ".$shipTime."\n".trans('generic.price', [], $this->player->lang).": ".
                               $shipPrice."\n".$firePowerString."\n".$shieldString."\n".$hullString."\n".$capacityString."\n".$speedString."\n".$crewString,
                    'inline' => true
                );
            }
            else
            {
                $embed['fields'][] = array(
                    'name' => trans('shipyard.hidden', [], $this->player->lang),
                    'value' => $requirementString,
                    'inline' => true
                );
            }
        }

        return $embed;
    }

    public function getQueue()
    {
        try
        {
            $displayList = $this->shipQueue->skip(5*($this->page -1))->take(5);

            $shipQueueString = "";
            foreach($displayList as $queuedShip)
            {
                $now = Carbon::now();
                $buildTime = $now->diffForHumans($queuedShip->pivot->ship_end,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);

                $shipQueueString .= "1x ".$queuedShip->name." - ".$buildTime."\n";
            }

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('shipyard.shipQueue', [], $this->player->lang),
                "description" => $shipQueueString,
                'fields' => [],
                'footer' => array(
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

}
