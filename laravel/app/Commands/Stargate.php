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
use App\Trade;
use App\TradeResource;
use App\SpyLog;

class Stargate extends CommandHandler implements CommandInterface
{
    public $listner;
    public $paginatorMessage;
    public $tradeResources;
    public $maxTime;
    public $coordinateDestination;
    public $attackMilitaries;
    public $attackUnits;

    public function execute()
    {
        if(!is_null($this->player))
        {
            try{
                echo PHP_EOL.'Execute Stargate';
                if($this->player->ban)
                    return trans('generic.banned', [], $this->player->lang);
                    
                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                $researchCenter = Building::find(7);
                $centerLevel = $this->player->activeColony->hasBuilding($researchCenter);
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
                $this->coordinateDestination = $coordinate = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();

                if(is_null($coordinate))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                if(!is_null($coordinate->colony) && $this->player->user_id != 125641223544373248)
                {
                    $researchCenter = Building::find(7);
                    $centerLevel = $coordinate->colony->hasBuilding($researchCenter);
                    if(!$centerLevel || $centerLevel < 4)
                        return trans('stargate.failedDialing', [], $this->player->lang);
                }

                if($coordinate->id == $this->player->activeColony->coordinates->id && $this->player->user_id != 125641223544373248)
                    return trans('stargate.failedDialing', [], $this->player->lang);

                //Check Consommation E2PZ
                $travelCost = $this->getConsumption($this->player->activeColony->coordinates,$coordinate);
                if($this->player->activeColony->E2PZ < $travelCost)
                    return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost-$this->player->activeColony->E2PZ,2)], $this->player->lang);

                if(Str::startsWith('explore',$this->args[0]))
                {
                    if(!is_null($coordinate->colony))
                        return trans('stargate.explorePlayerImpossible', [], $this->player->lang);

                    if($this->player->activeColony->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.round(1000-$this->player->activeColony->military,2)], $this->player->lang);

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
                    
                    $this->player->activeColony->military -= 1000;
                    $this->player->activeColony->E2PZ -= $travelCost;
                    $this->player->activeColony->save();

                    $exploration = new Exploration;
                    $exploration->player_id = $this->player->id;
                    $exploration->coordinate_source_id = $this->player->activeColony->coordinates->id;
                    $exploration->coordinate_destination_id = $coordinate->id;
                    $exploration->exploration_end = Carbon::now()->addMinutes(rand(60,240));
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
                    $tradeString = "";
                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if((int)$this->args[$cptRes+1] > 0)
                                $qty = $this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            $resource = $this->args[$cptRes];
                            if(Str::startsWith('e2pz',$resource) || Str::startsWith('zpm',$resource) || Str::startsWith('ZPM',$resource))
                                $resource = 'E2PZ';

                            $resFound = false;
                            foreach($availableResources as $availableResource)
                            {
                                if(Str::startsWith($availableResource,$resource))
                                {
                                    $resource = $availableResource;

                                    $resFound = true;
                                    if($resource != 'military')
                                        $capacityNeeded += $qty;
                                    $tradeString .= config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.number_format($qty)."\n";

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
                                    $tradeString .= $unit->name.': '.number_format($qty)."\n";
                                }
                            }
                            $this->tradeResources[$resource] = $qty;
                        }
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);
                    }

                    $tradeCapacity = $this->player->activeColony->tradeCapacity();
                    if($tradeCapacity < $capacityNeeded)
                        return trans('generic.notEnoughCapacity', ['missingCapacity' => number_format(round($capacityNeeded - $tradeCapacity))], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();

                    $tradeMsg = trans('stargate.tradeMessage', ['coordinateDestination' => $destCoordinates, 'coordinateSource' => $sourceCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name, 'resources' => $tradeString, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($tradeMsg)->then(function ($messageSent) use($travelCost){
                        
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){ 
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){ 
                            });
                        });
    
                        $this->listner = function ($messageReaction) use ($travelCost){
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    echo 'CONFIRMED'; 
                                    $receivedString = "";
                                    $tradeObjets = [];
                                    foreach($this->tradeResources as $tradeResource => $qty)
                                    {
                                        $unit = Unit::Where('slug', $tradeResource)->first();
                                        if(!is_null($unit))
                                        {
                                            $tradeObjets[] = ['unit_id' => $unit->id,'quantity'=>$qty];
                                            $ownedUnits = $this->player->activeColony->hasCraft($unit);
                                            if(!$ownedUnits)
                                            {
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => $unit->name.': '.number_format($qty)], $this->player->lang));
                                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content));
                                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                                return;
                                            }
                                            elseif($ownedUnits < $qty)
                                            {
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => $unit->name.': '.number_format($qty-$ownedUnits)], $this->player->lang));
                                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content));
                                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                                return;
                                            }
                                            $receivedString .= $unit->name.': '.number_format($qty)."\n";
                                        }        
                                        elseif($this->player->activeColony->$tradeResource < $qty)
                                        {
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($tradeResource))." ".ucfirst($tradeResource).': '.number_format(round($qty-$this->player->activeColony->$tradeResource))], $this->player->lang));
                                            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content));
                                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                            return;
                                        }
                                        else
                                        {
                                            $tradeObjets[] = ['resource' => $tradeResource,'quantity'=>$qty];
                                            $receivedString .= config('stargate.emotes.'.strtolower($tradeResource))." ".ucfirst($tradeResource).': '.number_format($qty)."\n";
                                        }                           
                                    }     

                                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                    $embed = [
                                        'author' => [
                                            'name' => $this->coordinateDestination->colony->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                                        ],
                                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/bouteille.gif'],
                                        "title" => "Stargate",
                                        "description" => trans('stargate.tradeReceived', ['coordinateDestination' => $destCoordinates, 'coordinateSource' => $sourceCoordinates, 'player' => $this->player->user_name, 'resources' => $receivedString], $this->coordinateDestination->colony->player->lang),
                                        'fields' => [
                                        ],
                                        'footer' => array(
                                            'text'  => 'Stargate',
                                        ),
                                    ];

                                    $userExist = $this->discord->users->filter(function ($value){
                                        return $value->id == $this->coordinateDestination->colony->player->user_id;
                                    });
                                    if($userExist->count() > 0)
                                    {
                                        $foundUser = $userExist->first();
                                        $foundUser->sendMessage('', false, $embed);
                                    }

                                    $userExist = $this->discord->users->filter(function ($value){
                                        return $value->id == $this->player->user_id;
                                    });
                                    if($userExist->count() > 0)
                                    {
                                        $foundUser = $userExist->first();
                                        $foundUser->sendMessage(trans('stargate.tradeSent',['coordinateDestination' => $destCoordinates, 'coordinateSource' => $sourceCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name, 'resources' => $receivedString, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang));
                                    }

                                    try{
                                        $tradeLog = new Trade;
                                        $tradeLog->source_player_id = $this->player->id;
                                        $tradeLog->coordinate_source_id = $this->player->activeColony->coordinates->id;
                                        $tradeLog->dest_player_id = $this->coordinateDestination->colony->player->id;
                                        $tradeLog->coordinate_destination_id = $this->coordinateDestination->id;
                                        $tradeLog->trade_value = 0;
                                        $tradeLog->save();

                                        foreach($tradeObjets as $tradeObject)
                                        {
                                            $tradeResource = new TradeResource;
                                            $tradeResource->trade_id = $tradeLog->id;
                                            $tradeResource->quantity = $tradeObject['quantity'];
                                            if(isset($tradeObject['unit_id']))
                                            {
                                                $tradeResource->unit_id = $tradeObject['unit_id'];
                                                $tradeResource->load('unit');
                                                
                                                $unitExists = $this->coordinateDestination->colony->units->filter(function ($value) use($tradeResource){               
                                                    return $value->id == $tradeResource->unit->id;
                                                });
                                                if($unitExists->count() > 0)
                                                {
                                                    $unitToUpdate = $unitExists->first();
                                                    $unitToUpdate->pivot->number += $tradeResource->quantity;
                                                    $unitToUpdate->pivot->save();
                                                }
                                                else
                                                {
                                                    $this->coordinateDestination->colony->units()->attach([$tradeResource->unit_id => ['number' => $tradeResource->quantity]]);
                                                }
                                                $unitExists = $this->player->activeColony->units->filter(function ($value) use($tradeResource){               
                                                    return $value->id == $tradeResource->unit->id;
                                                });
                                                $unitTodown = $unitExists->first();
                                                $unitTodown->pivot->number -= $tradeResource->quantity;
                                                $unitTodown->pivot->save();
                                            }
                                            elseif(isset($tradeObject['resource']))
                                            {
                                                $tradeResource->resource = $tradeObject['resource'];
                                                $this->player->activeColony->{$tradeObject['resource']} -= $tradeObject['quantity'];
                                                $this->coordinateDestination->colony->{$tradeObject['resource']} += $tradeObject['quantity'];
                                            }
                                            $tradeResource->setValue();
                                            $tradeResource->save();
                                        }

                                        $this->player->activeColony->E2PZ -= $travelCost;
                                        $this->player->activeColony->save();
                                        $this->coordinateDestination->colony->save();

                                        $tradeLog->load('tradeResources');
                                        $tradeLog->setTradeValue();
                                        $tradeLog->save();
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }

                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content));
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    echo 'CANCELLED'; 
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content));
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

                    $malp = Unit::where('slug', 'malp')->first();
                    $malpNumber = $this->player->activeColony->hasCraft($malp);
                    if(!$malpNumber)
                        return trans('generic.notEnoughResources', ['missingResources' => $malp->name.': 1'], $this->player->lang);
                    elseif($malpNumber == 0)
                        return trans('generic.notEnoughResources', ['missingResources' => $malp->name.': 1'], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $spyMessage = trans('stargate.spyConfirmation', ['coordinateDestination' => $destCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3).' '.$malp->name.': 1'], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($spyMessage)->then(function ($messageSent) use($travelCost,$sourceCoordinates,$destCoordinates,$malp){
                        
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){ 
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){ 
                            });
                        });
    
                        $this->listner = function ($messageReaction) use ($travelCost,$sourceCoordinates,$destCoordinates,$malp){
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    try{


                                    $current = Carbon::now();
                                    $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->coordinateDestination->colony->last_claim);
                                    if($current->diffInMinutes($lastClaim) > 720){
                                        $this->coordinateDestination->colony->checkColony();
                                        $this->coordinateDestination->load('colony');
                                    }

                                    $this->player->activeColony->E2PZ -= $travelCost;
                                    $this->player->save();

                                    $malpExists = $this->player->activeColony->units->filter(function ($value){               
                                        return $value->slug == 'malp';
                                    });
                                    if($malpExists->count() > 0)
                                    {
                                        $unitToUpdate = $malpExists->first();
                                        $unitToUpdate->pivot->number -= 1;
                                        $unitToUpdate->pivot->save();
                                    }

                                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                    $spyConfirmedMessage = trans('stargate.spySending', ['coordinateDestination' => $destCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3).' '.$malp->name.': 1'], $this->player->lang);

                                    $embed = [
                                        'author' => [
                                            'name' => $this->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                                        ],
                                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpSending.gif'],
                                        "title" => "Stargate",
                                        "description" => $spyConfirmedMessage,
                                        'fields' => [
                                        ],
                                        'footer' => array(
                                            'text'  => 'Stargate',
                                        ),
                                    ];
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, '', $embed);


                                    $userExist = $this->discord->users->filter(function ($value){
                                        return $value->id == $this->coordinateDestination->colony->player->user_id;
                                    });
                                    if($userExist->count() > 0)
                                    {
                                        $foundUser = $userExist->first();

                                        //Vous avez été scan
                                        $foundUser->sendMessage(trans('stargate.messageSpied', ['sourceCoordinates' => $sourceCoordinates, 'player' => $this->player->user_name], $this->player->lang));
                                    }

                                    $userExist = $this->discord->users->filter(function ($value){
                                        return $value->id == $this->player->user_id;
                                    });
                                    if($userExist->count() > 0)
                                    {
                                        $foundUser = $userExist->first();

                                        try{

                                        $spyLog = new SpyLog;
                                        $spyLog->source_player_id = $this->player->id;
                                        $spyLog->coordinate_source_id = $this->player->activeColony->coordinates->id;
                                        $spyLog->dest_player_id = $this->coordinateDestination->colony->player->id;
                                        $spyLog->coordinate_destination_id = $this->coordinateDestination->id;
                                        $spyLog->save();

                                        $spy = Technology::where('slug', 'spy')->first();
                                        $counterSpy = Technology::where('slug', 'counterspy')->first();
                    
                                        $playerDestination = $this->coordinateDestination->colony->player;
                                        $spyLvl = $this->player->hasTechnology($spy);
                                        $counterSpyLvl = $playerDestination->hasTechnology($counterSpy);
                    
                                        if(!$spyLvl)
                                            $spyLvl = 0;
                                        if(!$counterSpyLvl)
                                            $counterSpyLvl = 0;
                    
                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                                            ],
                                            'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpScreen.jpg'],
                                            "title" => "Stargate",
                                            "description" => trans('stargate.spyReportDescription', ['coordinateDestination' => $destCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name], $this->player->lang),
                                            'fields' => [
                                            ],
                                            'footer' => array(
                                                'text'  => 'Stargate',
                                            ),
                                        ];
                    
                                        $showResources = $showFleet = $showDefenses = $showBuildings = $showMilitaries = $showTechnologies = false;
                    
                                        if($spyLvl < $counterSpyLvl && ($counterSpyLvl-$spyLvl) >= 4)
                                        {
                                            $embed['fields'][] = [
                                                'name' => trans('stargate.emptyReportTitle', [], $this->player->lang),
                                                'value' => trans('stargate.technologyTooLow', [], $this->player->lang),
                                            ];
                                        }

                                        elseif($spyLvl <= $counterSpyLvl && ($counterSpyLvl-$spyLvl) >= 0)
                                            $showResources = true;
                                        elseif($spyLvl > $counterSpyLvl)
                                        {
                                            $showResources = true;
                                            if(($spyLvl-$counterSpyLvl) >= 1)
                                                $showFleet = true;
                                            if(($spyLvl-$counterSpyLvl) >= 2)
                                                $showDefenses = true;
                                            if(($spyLvl-$counterSpyLvl) >= 3)
                                                $showBuildings = $showMilitaries = true;
                                            if(($spyLvl-$counterSpyLvl) >= 4)
                                                $showTechnologies = true;
                                        }
                    
                                        if($showResources)
                                        {
                                            $resourceString = "";
                                            foreach(config('stargate.resources') as $resource){
                                                $resourceString .= config('stargate.emotes.'.strtolower($resource)).' '.ucfirst($resource).": ".number_format($this->coordinateDestination->colony->$resource).' ';
                                            }
                                            //$resourceString .= config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format($this->coordinateDestination->colony->E2PZ);
                    
                                            $embed['fields'][] = [
                                                'name' => trans('generic.resources', [], $this->player->lang),
                                                'value' => $resourceString
                                            ];
                                        }
                    
                                        if($showFleet)
                                        {
                                            $embed['fields'][] = [
                                                'name' => trans('stargate.fleet', [], $this->player->lang),
                                                'value' => trans('stargate.emptyFleet', [], $this->player->lang)
                                            ];
                                        }
                    
                                        if($showDefenses)
                                        {
                                            $embed['fields'][] = [
                                                'name' => trans('stargate.defenses', [], $this->player->lang),
                                                'value' => trans('stargate.emptyDefenses', [], $this->player->lang)
                                            ];
                                        }
                    
                                        if($showBuildings)
                                        {
                                            $buildingString = "";
                                            foreach($this->coordinateDestination->colony->buildings as $building)
                                            {
                                                if(!empty($buildingString))
                                                    $buildingString .= ', ';
                                                $buildingString .= $building->name.' ('.$building->pivot->level.')';
                                            }
                                            if(empty($buildingString))
                                                $buildingString = 'Aucun bâtiment';
                                            $embed['fields'][] = [
                                                'name' => trans('stargate.buildings', [], $this->player->lang),
                                                'value' => $buildingString
                                            ];
                                        }
                    
                                        if($showMilitaries)
                                        {
                                            $militaryString = config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format($this->coordinateDestination->colony->military);
                                            foreach($this->coordinateDestination->colony->units as $unit)
                                            {
                                                $militaryString .= ', ';
                                                $militaryString .= $unit->name.' ('.number_format($unit->pivot->number).')';
                                            }
                                            $embed['fields'][] = [
                                                'name' => trans('generic.militaries', [], $this->player->lang),
                                                'value' => $militaryString
                                            ];
                                        }
                    
                                        if($showTechnologies)
                                        {
                                            $technologyString = "";
                                            foreach($playerDestination->technologies as $technology)
                                            {
                                                if(!empty($technologyString))
                                                    $technologyString .= ', ';
                                                $technologyString .= $technology->name.' ('.$technology->pivot->level.')';
                                            }
                                            if(empty($technologyString))
                                                $technologyString = "Aucune technologie";
                                            $embed['fields'][] = [
                                                'name' => trans('generic.technologies', [], $this->player->lang),
                                                'value' => $technologyString
                                            ];
                                        }
                    
                                        $foundUser->sendMessage('', false, $embed);

                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }


                                    }

                                    


                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }

                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,trans('stargate.spyCancelled', [], $this->player->lang), null);
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                }
                            }
                        };
                        $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                    });
                }

                if(Str::startsWith('colonize',$this->args[0]))
                {
                    if(!is_null($this->coordinateDestination->colony))
                        return trans('stargate.playerOwned', [], $this->player->lang);
                        
                    if($this->player->user_id != 125641223544373248)
                        return 'Under Developement';         

                    if($this->player->activeColony->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.round(1000-$this->player->activeColony->military,2)], $this->player->lang);

                    if($this->player->colonies->count() < config('stargate.maxColonies'))
                    {
                        $this->player->activeColony->military -= 1000;
                        $this->player->activeColony->E2PZ -= $travelCost;
                        $this->player->activeColony->save();

                        $this->player->addColony($this->coordinateDestination);
                        return trans('stargate.colonizeDone', [], $this->player->lang);
                    }
                    else
                    {
                        return trans('stargate.toManyColonies', [], $this->player->lang);
                    }

                }

                if(Str::startsWith('attack',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    if($this->player->user_id != 125641223544373248)
                        return 'Under Developement';     

                    /*
                    if($this->player->activeColony->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.round(100-$this->player->activeColony->military,2)], $this->player->lang);
                    */

                    /**
                     * Voulez vous attacker machin depuis marchin avec:
                     * 
                     * detail
                     * 
                     * ppour un cout de: travelCost
                     */
                    $capacityNeeded = 0;
                    $attackConfirmPower = "";
                    $this->attackMilitaries = 0;
                    $this->attackUnits = [];
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
                            if(Str::startsWith('military',$resource))
                            {
                                $resource = 'military';
                                $this->attackMilitaries = $qty;
                                $attackConfirmPower .= config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.number_format($qty)."\n";
                            }
                            else
                            {
                                $unit = Unit::Where('slug', 'LIKE', $resource.'%')->first();
                                if(is_null($unit))
                                    return trans('stargate.unknownResource', ['resource' => $resource], $this->player->lang);
                                else
                                {
                                    $resFound = true;
                                    $resource = $unit->slug;
                                    $attackConfirmPower .= $unit->name.': '.number_format($qty)."\n";
                                    $this->attackUnits[] = ['qty' => $qty, 'unit' => $unit];
                                }
                            }
                        }
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);
                    }
                    if($this->attackMilitaries < 100)
                    {
                        if($this->attackMilitaries < 100)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.round(100-$this->player->activeColony->military,2)], $this->player->lang);
                    }

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $spyMessage = trans('stargate.AttackConfirmation', ['militaryUnits' => $attackConfirmPower,'coordinateDestination' => $destCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3).' '.$malp->name.': 1'], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($spyMessage)->then(function ($messageSent) use($travelCost,$sourceCoordinates,$destCoordinates,$malp){
                        
                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){ 
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){ 
                            });
                        });
    
                        $this->listner = function ($messageReaction) use ($travelCost,$sourceCoordinates,$destCoordinates,$malp){
                            if($this->maxTime < time())
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
    
                            if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                            {
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    try{



                                        /** 
                                         * 
                                         * 
                                         * 
                                         * 
                                         * 
                                         * 
                                         * Vérifier que le joueur à assez de ce qu'il indique 
                                         * 
                                         * 
                                         * 
                                         * 
                                         * 
                                         * 
                                         * */


                                        $current = Carbon::now();
                                        $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->coordinateDestination->colony->last_claim);
                                        if($current->diffInMinutes($lastClaim) > 720){
                                            $this->coordinateDestination->colony->checkColony();
                                            $this->coordinateDestination->load('colony');
                                        }
                                        $this->player->activeColony->E2PZ -= $travelCost;
                                        $this->player->save();

                                        $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                                        $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                        $attackSentMessage = trans('stargate.attackSent', ['coordinateDestination' => $destCoordinates, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3).' '.$malp->name.': 1'], $this->player->lang);

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                                            ],
                                            'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpSending.gif'],
                                            "title" => "Stargate",
                                            "description" => $attackSentMessage,
                                            'fields' => [
                                            ],
                                            'footer' => array(
                                                'text'  => 'Stargate',
                                            ),
                                        ];
                                        $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, '', $embed);


                                        /*CHECK LA DEFENSE => 1 point d attaque => 20 military */

                                        /**Check resources plus haut */

                                        /* RAPPORT D ATTAQUE 
                                        
                                        Pertes
                                         (Nb Mili Défenseur)² / (Nb Mili Attaquant)

                                         Gains
                                          (Nb Mili Défenseur)/5


                                          Pille jusqu'à 60M% selon capacités, proportionnel
                                        */


                                        /* RAPPORT DE DEFESSE 
                                        Vous avez été attaqué
                                        -N'ont rien pillé
                                        =>vous perdez...
                                        =>vous gagnez x transport/militaires
                                        -pillé
                                        =>Vous avez perdu x ressources / militaires 
                                        */



                                
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }

                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,trans('stargate.spyCancelled', [], $this->player->lang), null);
                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                                }
                            }
                        };
                        $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                    });
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
