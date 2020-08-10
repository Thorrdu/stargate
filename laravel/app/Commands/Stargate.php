<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Building;
use App\Technology;
use App\Coordinate;
use App\Unit;
use App\Exploration;
use Carbon\Carbon;


class Stargate extends CommandHandler implements CommandInterface
{
    public $listner;
    public $paginatorMessage;
    public $tradeResources;
    public $maxTime;

    public function execute()
    {
        if(!is_null($this->player))
        {
            try{
                echo PHP_EOL.'Execute Stargate';
                if($this->player->ban)
                    return trans('generic.banned', [], $this->player->lang);

                $researchCenter = Building::find(7);
                $centerLevel = $this->player->colonies[0]->hasBuilding($researchCenter);
                if(!$centerLevel || $centerLevel < 5)
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/disabledStargate.jpg'],
                        "title" => "Stargate",
                        "description" => trans('stargate.stargateShattered', [], $this->player->lang),
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];
                    $this->message->channel->sendMessage('', false, $embed);
                    return;
                }
                
                if(count($this->args) < 2)
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/enabledStargate.jpg'],
                        "title" => "Stargate",
                        "description" => trans('stargate.askBaseParameter', [], $this->player->lang),
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];
                    $this->message->channel->sendMessage('', false, $embed);
                    return;
                }
                if(!preg_match('/[0-9]{1,}:[0-9]{1,}:[0-9]{1,}/', $this->args[1], $coordinatesMatch))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                //Est-ce que la destination à une porte ?
                $coordinates = explode(':',$coordinatesMatch[0]);
                $coordinate = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();

                if(is_null($coordinate))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                if(!is_null($coordinate->colony) && $this->player->user_id != 125641223544373248)
                {
                    $researchCenter = Building::find(7);
                    $centerLevel = $coordinate->colony->hasBuilding($researchCenter);
                    if(!$centerLevel || $centerLevel < 4)
                        return trans('stargate.failedDialing', [], $this->player->lang);
                }

                if($coordinate->id == $this->player->colonies[0]->coordinates->id)
                    return trans('stargate.failedDialing', [], $this->player->lang);


                //Check Consommation E2PZ
                $travelCost = $this->getConsumption($this->player->colonies[0]->coordinates,$coordinate);
                if($this->player->colonies[0]->E2PZ < $travelCost)
                    return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost-$this->player->colonies[0]->E2PZ,2)], $this->player->lang);

                if(Str::startsWith('explore',$this->args[0]))
                {
                    if(!is_null($coordinate->colony))
                        return trans('stargate.explorePlayerImpossible', [], $this->player->lang);

                    if($this->player->colonies[0]->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.round(1000-$this->player->colonies[0]->military,2)], $this->player->lang);

                    if($this->player->explorations->count() > 0)
                    {
                        $lastExploration = $this->player->explorations->last();
                        $lastExplorationCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$lastExploration->exploration_end);
                        if(!$lastExplorationCarbon->isPast())
                            return trans('stargate.alreadyExploring', [], $this->player->lang);

                        $alreadyExplored = $this->player->explorations->filter(function ($value) use($coordinate){
                            return $value->coordinateDestination->id == $coordinate->id;
                        });
                        if($alreadyExplored->count() > 0)
                            return trans('stargate.alreadyExplored', [], $this->player->lang);
                    }
                    
                    $this->player->colonies[0]->military -= 1000;
                    $this->player->colonies[0]->E2PZ -= $travelCost;
                    $this->player->colonies[0]->save();

                    $exploration = new Exploration;
                    $exploration->player_id = $this->player->id;
                    $exploration->coordinate_source_id = $this->player->colonies[0]->coordinates->id;
                    $exploration->coordinate_destination_id = $coordinate->id;
                    $exploration->exploration_end = Carbon::now()->addMinutes(rand(60,300));
                    $exploration->save();
                    
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/exploration.gif'],
                        "title" => "Stargate",
                        "description" => trans('stargate.explorationSent', ['coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang),
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];
                    $this->message->channel->sendMessage('', false, $embed);
                    return;
                }

                if(Str::startsWith('trade',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.tradeNpcImpossible', [], $this->player->lang);

                    if(count($this->args) < 4)
                        return trans('stargate.missingParameters', [], $this->player->lang).' / !s trade [Coordinates] Ress1 Qty1';

                    $availableResources = config('stargate.resources');
                    $availableResources[] = 'E2PZ';
                    $availableResources[] = 'military';
                        
                    $this->tradeResources = [];
                    $capacityNeeded = 0;
                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if((int)$this->args[$cptRes+1] > 0)
                                $qty = $this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            $resource = $this->args[$cptRes];
                            $resFound = false;
                            foreach($availableResources as $availableResource)
                            {
                                if(Str::startsWith($availableResource,$resource))
                                {
                                    $resFound = true;
                                    $resource = $availableResource;
                                    $capacityNeeded += $qty;
                                }
                            }
                            if(!$resFound)
                            {
                                $unit = Unit::Where('slug', 'LIKE', $resource.'%')->first();
                                if(is_null($unit))
                                    return trans('stargate.unknownResource', ['resource' => $resource], $this->player->lang);
                                else
                                {
                                    $resFound = true;
                                    $resource = $unit->slug;
                                }
                            }
                            $this->tradeResources[$resource] = $qty;
                        }
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);
                    }

                    $tradeCapacity = $this->player->colonies[0]->tradeCapacity();
                    if($tradeCapacity < $capacityNeeded)
                        return trans('generic.notEnoughCapacity', ['missingCapacity' => number_format(round($capacityNeeded - $tradeCapacity))], $this->player->lang);

                    $tradeString = "";
                    foreach($this->tradeResources as $tradeResource => $qty)
                        $tradeString .= $tradeResource.': '.$qty." ";

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($tradeString)->then(function ($messageSent){
                        
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){ 
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){ 
                            });
                        });
    
                        $this->listner = function ($messageReaction){
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    echo 'CONFIRMED'; 

                                    foreach($this->tradeResources as $tradeResource => $qty)
                                    {
                                        $unit = Unit::Where('slug', $tradeResource)->first();
                                        if(!is_null($unit))
                                        {
                                            $ownedUnits = $this->player->colonies[0]->hasCraft($unit);
                                            if(!$ownedUnits)
                                            {
                                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,trans('generic.notEnoughResources', ['missingResources' => $unit->name.': '.number_format($qty)], $this->player->lang));
                                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                                return;
                                            }
                                            elseif($ownedUnits < $qty)
                                            {
                                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,trans('generic.notEnoughResources', ['missingResources' => $unit->name.': '.number_format($qty-$ownedUnits)], $this->player->lang));
                                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                                return;
                                            }
                                        }        
                                        elseif($this->player->colonies[0]->$tradeResource < $qty)
                                        {
                                            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.$tradeResource)." ".ucfirst($tradeResource).': '.number_format(round($qty-$this->player->colonies[0]->$tradeResource))], $this->player->lang));
                                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                            return;
                                        }                             
                                    }     

                                    /**
                                    * 
                                    * 
                                    * 
                                    * 
                                    * Déplacement des ressources
                                    *
                                    *
                                    *
                                    *
                                    *LOG
                                    *Message aux 2 parties
                                    */



                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'Confirmed');
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    echo 'CANCELLED'; 
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,trans('stargate.tradeCancelled', [], $this->player->lang));
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                }
                            }
                        };
                        $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                    });


                    return;
                }

                if(Str::startsWith('spy',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    return 'Under developement';
                }
                
                if(Str::startsWith('attack',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);
                        
                    return 'Under developement';
                }
            }
            catch(\Exception $e)
            {
                return $e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }

    public function getConsumption(Coordinate $source,Coordinate $destination)
    {
        //0.03 * system 
        //3 * galaxy
        if($source->galaxy != $destination->galaxy)
            return abs($source->galaxy - $destination->galaxy)*3;
        else
            return abs($source->system - $destination->system)*0.03;
    }
}
