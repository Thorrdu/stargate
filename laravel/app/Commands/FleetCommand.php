<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Building;
use App\Technology;
use App\Coordinate;
use App\Fleet;
use App\Unit;
use App\Ship;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Trade;
use App\TradeResource;
use App\SpyLog;
use App\GateFight;
use App\Reminder;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;

class FleetCommand extends CommandHandler implements CommandInterface
{
    public $listner;
    public $paginatorMessage;
    public $transportResources;
    public $maxTime;
    public $coordinateDestination;
    public $baseTravelCost;
    public $travelCost;
    public $closed;
    public $fleet;
    public $fleetShips;
    public $transportString;
    public $fleetMaxSpeed;
    public $fleetSpeed;
    public $usedCapacity;

    public function execute()
    {
        if(!is_null($this->player))
        {
            try{
                echo PHP_EOL.'Execute FleetCommand';
                if($this->player->ban)
                    return trans('generic.banned', [], $this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $this->player->checkFleets();

                $comTechnology = Technology::find(9); // Shipyard
                $currentComTechLvl = $this->player->hasTechnology($comTechnology);
                if(!$currentComTechLvl)
                {
                    return trans('fleet.missingComTech', [], $this->player->lang);
                }

                if(count($this->args) < 2)
                {
                    /**
                     * 08min 59s 	40 de vos vaisseaux se dirigent vers Melbourne [4:67:5]. Leur mission est " Attaquer " et leur origine est Asgard [5:25:3].
                     * 03j 06h 49min 29s 	1 de vos vaisseaux se dirige vers Colonie [4:28:4]. Sa mission est " Transporter " et son origine est Asgard [5:25:3] .
                     * 07min 30s 	40 de vos vaisseaux se dirigent vers Asgard [5:25:3]. Leur mission est " Retour " et leur origine est Melbourne [4:67:5].
                     */

                    $activeFleetsString = '';
                    $now = Carbon::now();
                    foreach($this->player->activeFleets as $activeFleet)
                    {
                        if($activeFleet->returning)
                        {
                            $fleetStatus = trans('fleet.returningStatus', [], $this->player->lang);
                            $sourceColony = $activeFleet->sourceColony;
                            $destinationColony = $activeFleet->destinationColony;
                        }
                        else
                        {
                            $fleetStatus = trans('fleet.ongoingStatus', [], $this->player->lang);
                            $sourceColony = $activeFleet->destinationColony;
                            $destinationColony = $activeFleet->sourceColony;
                        }

                        $arrivalDateCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$activeFleet->arrival_date);
                        $arrivalDate = $now->diffForHumans($arrivalDateCarbon,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        $activeFleetsString .= $arrivalDate.' - '.trans('fleet.activeFleet', [
                                                                        'mission' => ucfirst($activeFleet->mission),
                                                                        'id' => $activeFleet->id,
                                                                        'status' => $fleetStatus,
                                                                        'shipCount' => $activeFleet->shipCount(),
                                                                        'colonySource' => $sourceColony->name,
                                                                        'coordinatesSource' => $sourceColony->coordinates->humanCoordinates(),
                                                                        'colonyDest' => $destinationColony->name,
                                                                        'coordinatesDest' => $destinationColony->coordinates->humanCoordinates(),
                                                                        ], $this->player->lang)."\n";
                    } //source
                    if(empty($activeFleetsString))
                        $activeFleetsString = trans('fleet.noActiveFleet', [], $this->player->lang)."\n";

                    $incomingFleetString = '';
                    foreach($this->player->incomingFleets as $incomingFleet)
                    {
                        if($incomingFleet->player_source_id != $incomingFleet->player_destination_id && !$incomingFleet->returning)
                        {
                            $sourceColony = $incomingFleet->sourceColony;
                            $destinationColony = $incomingFleet->destinationColony;

                            $arrivalDateCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$incomingFleet->arrival_date);
                            $arrivalDate = $now->diffForHumans($arrivalDateCarbon,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);

                            $incomingFleetString .= trans('fleet.incomingFleet', ['mission' => $activeFleet->mission,
                                                                            'shipCount' => $incomingFleet->shipCount(),
                                                                            'colonySource' => $sourceColony->name,
                                                                            'coordinatesSource' => $sourceColony->coordinates->humanCoordinates(),
                                                                            'colonyDest' => $destinationColony->name,
                                                                            'coordinatesDest' => $destinationColony->coordinates->humanCoordinates(),
                                                                            ], $this->player->lang);
                        }
                    } //dest
                    if(empty($incomingFleetString))
                        $incomingFleetString = trans('fleet.noIncomingFleet', [], $this->player->lang)."\n";

                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/fleet.jpg'],
                        "title" => "Stargate",
                        "description" => trans('fleet.activeFleets', ['fleets' => $activeFleetsString], $this->player->lang)."\n".trans('fleet.incomingFleets', ['fleets' => $incomingFleetString], $this->player->lang)."\n".trans('fleet.askBaseParameter', [], $this->player->lang),
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];
                    $newEmbed = $this->discord->factory(Embed::class,$embed);
                    $this->message->channel->sendMessage('', false, $newEmbed);
                    return;
                }
                elseif(!Str::startsWith('base',$this->args[0]) && !Str::startsWith('transport',$this->args[0])
                && !Str::startsWith('colonize',$this->args[0]) && !Str::startsWith('order',$this->args[0]))
                {
                    return trans('fleet.wrongParameter', [], $this->player->lang);
                }

                if(Str::startsWith('colonize',$this->args[0]) || Str::startsWith('attack',$this->args[0]))
                    return 'Not yet implemented';

                if(Str::startsWith('order',$this->args[0]))
                {
                    $fleetId = (int)$this->args[1];
                    $fleetExists = $this->player->activeFleets->filter(function ($value) use($fleetId){
                        return $value->id == $fleetId;
                    });
                    if($fleetExists->count() > 0)
                    {
                        $fleetControl = $fleetExists->first();
                    }
                    else
                        return trans('fleet.unknownFleet', [], $this->player->lang);

                    if(isset($this->args[2]) && Str::startsWith('return',$this->args[2]))
                    {
                        if($fleetControl->returning)
                            return trans('fleet.alreadyReturning', [], $this->player->lang);

                        $now = Carbon::now();
                        $departureDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleetControl->departure_date);
                        $newArrivalDate = $departureDate->diffInSeconds($now);

                        $fleetControl->arrival_date = Carbon::now()->addSeconds($newArrivalDate);
                        $fleetControl->returning = true;
                        $fleetControl->save();

                        $now = Carbon::now();
                        $fleetDuration = $now->diffForHumans($fleetControl->arrival_date,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        return trans('fleet.fleetReturning', ['duration',
                                                            'planetSource' => $fleetControl->sourceColony->name,
                                                            'coordinateSource' => $fleetControl->sourceColony->coordinates->humanCoordinates()],
                                                            $this->player->lang);
                    }
                    else
                        return trans('fleet.wrongParameter', [], $this->player->lang);
                }

                if(!preg_match('/(([0-9]{1,}:[0-9]{1,}:[0-9]{1,})|([0-9]{1,};[0-9]{1,};[0-9]{1,}))/', $this->args[1], $coordinatesMatch))
                {
                    if((Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0])) && !((int)$this->args[1] > 0 && (int)$this->args[1] <= $this->player->colonies->count()))
                        return trans('colony.UnknownColony', [], $this->player->lang);
                    elseif(!Str::startsWith('base',$this->args[0]))
                        return trans('stargate.unknownCoordinates', [], $this->player->lang);

                    $this->coordinateDestination = $this->player->colonies[$this->args[1]-1]->coordinates;
                }
                else
                {
                    //Est-ce que la destination est à colonie ?
                    if(strstr($coordinatesMatch[0],';'))
                        $coordinates = explode(';',$coordinatesMatch[0]);
                    else
                        $coordinates = explode(':',$coordinatesMatch[0]);

                    $this->coordinateDestination = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();
                }

                if(is_null($this->coordinateDestination))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                if(!is_null($this->coordinateDestination->colony) && $this->player->user_id != 125641223544373248)
                {
                    if(!is_null($this->coordinateDestination->colony->player->vacation))
                        return trans('profile.playerVacation', [], $this->player->lang);
                }

                //&& $this->player->user_id != 125641223544373248
                if(!(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0])) && !is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id == $this->player->id )
                    return trans('stargate.samePlayerAction', [], $this->player->lang);

                if(Str::startsWith('base',$this->args[0]) && !is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id != $this->player->id)
                    return trans('stargate.notAColonyOfYour', [], $this->player->lang);

                if($this->coordinateDestination->id == $this->player->activeColony->coordinates->id && $this->player->user_id != 125641223544373248)
                    return trans('stargate.sameColony', [], $this->player->lang);

                //Base Fuel Consumption
                $this->baseTravelCost = $this->getConsumption($this->player->activeColony->coordinates,$this->coordinateDestination);
                $this->travelCost = $this->baseTravelCost;

                $fleetString = '';
                if(!Str::startsWith('spy',$this->args[0]))
                {
                    ///FLEET CONSTITUTION
                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang).' / !fleet [Order] [Coordinates] Ship1 Qty1 Ress1 Qty1';

                    $this->fleet = new Fleet;
                    $this->fleet->player_source_id = $this->player->id;
                    $this->fleet->colony_source_id = $this->player->activeColony->id;
                    $this->fleet->player_destination_id = $this->coordinateDestination->colony->player->id;
                    $this->fleet->colony_destination_id = $this->coordinateDestination->colony->id;

                    $this->fleet->departure_date = Carbon::now();
                    $this->fleet->crew = 0;
                    $this->fleet->capacity = 0;

                    $availableResources = config('stargate.resources');
                    $availableResources[] = 'E2PZ';
                    $availableResources[] = 'military';
                    $this->transportString = '';
                    $this->usedCapacity = 0;
                    $this->fleetMaxSpeed = 10;
                    $this->fleetSpeed = 100;
                    $this->fleetShips = array();

                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if((int)$this->args[$cptRes+1] > 0)
                                $qty = (int)$this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            if(Str::startsWith('speed',$this->args[$cptRes]))
                            {
                                if($qty > 100)
                                    $qty = 100;
                                if($qty < 10)
                                    $qty = 10;
                                $this->fleetSpeed = (int)$this->args[$cptRes+1];
                            }
                            else
                            {
                                $resourceName = $this->args[$cptRes];

                                $ship = $this->player->activeColony->hasShip($resourceName);
                                if(!$ship) // ship inconnu ou non présent
                                {
                                    if(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0]))
                                    {
                                        foreach($availableResources as $availableResource)
                                        {
                                            if(Str::startsWith($availableResource,$resourceName))
                                            {
                                                $resourceName = $availableResource;
                                                $this->fleet->$resourceName = $qty;
                                                $this->usedCapacity += $qty;

                                                if($resourceName != 'E2PZ' && $resourceName != 'military' && $this->coordinateDestination->colony->{'storage_'.$resourceName} < ($this->fleet->$resourceName))
                                                    return trans('stargate.transportStorageTooLow', ['resource' => config('stargate.emotes.'.strtolower($resourceName))." ".ucfirst($resourceName)], $this->player->lang);

                                                $this->transportString .= config('stargate.emotes.'.strtolower($resourceName))." ".ucfirst($resourceName).': '.number_format($qty)."\n";
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $fleetString .= $ship->name.': '.number_format($qty)."\n";

                                    //Check si possède
                                    if($ship->pivot->number < $qty)
                                        return trans('generic.notEnoughResources', ['missingResources' => $ship->name.': '.number_format($qty - $ship->pivot->number)], $this->player->lang);

                                    $this->fleet->crew += $ship->crew*$qty;
                                    $this->fleet->capacity += $ship->capacity*$qty;
                                    if($this->fleetMaxSpeed > $ship->speed)
                                        $this->fleetMaxSpeed = $ship->speed;

                                    $this->travelCost += $this->baseTravelCost * $ship->speed * $qty;

                                    $this->fleetShips[] = array('id' => $ship->id,'qty' => $qty);
                                }
                            }
                        }
                        else
                            return trans('fleet.wrongParameter', [], $this->player->lang);
                    }

                    if(empty($this->transportString))
                    {
                        if(Str::startsWith('transport',$this->args[0]))
                            return trans('stargate.noResourceSeleted', [], $this->player->lang);
                        else
                            $this->transportString = "/\n";
                    }

                    if(empty($this->fleetShips))
                        return trans('fleet.noShipSelected', [], $this->player->lang);

                    //check crew capacity
                    if($this->fleet->crew + $this->fleet->military > $this->player->activeColony->military)
                        return trans('generic.notEnoughResources', ['missingResources' => trans('shipyard.crew', ['crew' => number_format(ceil($this->fleet->crew - $this->player->activeColony->military))], $this->player->lang)], $this->player->lang);

                    $this->usedCapacity += $this->travelCost;
                    $this->usedCapacity += $this->fleet->crew;
                    //check fret capacity
                    if($this->fleet->capacity < $this->usedCapacity)
                        return trans('fleet.notEnoughCapacity', ['missingCapacity' => number_format(($this->usedCapacity) - $this->fleet->capacity)], $this->player->lang);

                    //check Speed
                    $this->fleetMaxSpeed = $this->fleetMaxSpeed * ($this->fleetSpeed/100);
                    $this->travelCost *= $this->fleetSpeed/100;

                    //check Carburant
                    if(($this->fleet->naqahdah + $this->travelCost) > $this->player->activeColony->naqahdah)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil($this->fleet->naqahdah - $this->player->activeColony->naqahdah))], $this->player->lang);

                    //Get arrivalDate
                    $travelTime = $this->fleet->getFleetTime($this->player->activeColony->coordinates, $this->coordinateDestination, $this->fleetMaxSpeed);
                    $this->fleet->arrival_date = Carbon::now()->add($travelTime.'s');
                }

                if(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0]))
                {
                    if(Str::startsWith('transport',$this->args[0])){
                        if($this->player->trade_ban)
                            return trans('stargate.trade_ban', [], $this->player->lang);

                        if($this->coordinateDestination->colony->player->trade_ban || $this->coordinateDestination->colony->player->ban)
                            return trans('stargate.playerTradeBan', [], $this->player->lang);

                        $this->fleet->mission = 'transport';
                    }
                    else
                        $this->fleet->mission = 'base';

                    if($this->coordinateDestination->colony->player->npc)
                        return trans('stargate.tradeNpcImpossible', [], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();

                    $now = Carbon::now();
                    $fleetDuration = $now->diffForHumans($this->fleet->arrival_date,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $baseMsg = trans('fleet.fleetMessage', [ 'mission' => ucfirst($this->fleet->mission),
                                                                'coordinateDestination' => $destCoordinates,
                                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                                'planetSource' => $this->player->activeColony->name,
                                                                'coordinateSource' => $sourceCoordinates,
                                                                'fleet' => $fleetString,
                                                                'freightCapacity' => number_format($this->usedCapacity).' / '.number_format($this->fleet->capacity),
                                                                'resources' => $this->transportString,
                                                                'crew' => number_format($this->fleet->crew),
                                                                'speed' => $this->fleetMaxSpeed,
                                                                'maxSpeed' => $this->fleetSpeed,
                                                                'fuel' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil($this->travelCost)),
                                                                'duration' => $fleetDuration,
                                                            ], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($baseMsg)->then(function ($messageSent){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });


                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    echo 'CONFIRMED';
                                    $this->player->activeColony->refresh();

                                    $availableResources = config('stargate.resources');
                                    $availableResources[] = 'E2PZ';
                                    $availableResources[] = 'military';

                                    //CHECK SHIPS
                                    try{
                                        foreach($this->fleetShips as $fleetShip)
                                        {
                                            $shipCheck = $this->player->activeColony->hasShipById($fleetShip['id']);
                                            if(!($shipCheck && $shipCheck->pivot->number >= $fleetShip['qty']))
                                            {
                                                $qtyOwned = 0;
                                                if($shipCheck)
                                                {
                                                    $qtyOwned = $shipCheck->pivot->number;
                                                    $resource = $shipCheck->name;
                                                }
                                                else
                                                {
                                                    $ship = Ship::find($fleetShip['id']);
                                                    $resource = $ship->name;
                                                }

                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.number_format(ceil($fleetShip['qty']-$qtyOwned))], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                        }

                                        //CHECK RESOURCES
                                        foreach(config('stargate.resources') as $resource)
                                        {
                                            $qtyNeeded = $this->fleet->$resource;
                                            if($resource == 'naqahdah')
                                                $qtyNeeded += $this->travelCost;

                                            if($qtyNeeded > 0)
                                            {
                                                if($this->player->activeColony->$resource >= $qtyNeeded)
                                                    $this->player->activeColony->$resource -= $qtyNeeded;
                                                else
                                                {
                                                    $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.number_format(ceil($qtyNeeded-$this->player->activeColony->$resource))], $this->player->lang));
                                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                    return;
                                                }
                                            }
                                        }

                                        $this->player->activeColony->save();
                                        $this->fleet->save();
                                        //addShipToFleet
                                        foreach($this->fleetShips as $fleetShip)
                                        {
                                            $this->fleet->ships()->attach([$fleetShip['id'] => ['number' => $fleetShip['qty']]]);
                                            $shipCheck = $this->player->activeColony->hasShipById($fleetShip['id']);
                                            $shipCheck->pivot->number -= $fleetShip['qty'];
                                            $shipCheck->pivot->save();
                                            //$this->activeColony->ships()->updateExistingPivot($fleetShip['id'], array('number' => 1), false);
                                        }
                                        echo '<br/>dddddd';

                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }
                                    echo '<br/>fffffff';
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    echo 'CANCELLED';
                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                }
                                $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                            }
                            catch(\Exception $e)
                            {
                                echo $e->getMessage();
                            }
                        });
                    });


                    return;
                }

                if(Str::startsWith('spy',$this->args[0]))
                {
                    if(is_null($this->coordinateDestination->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    if(!$this->player->isRaidable($this->coordinateDestination->colony->player) && $this->coordinateDestination->colony->player->npc == 0)
                        return trans('stargate.weakOrStrong', [], $this->player->lang);

                    $spyLast2Hours = SpyLog::Where([['source_player_id',$this->player->id],['colony_destination_id',$this->coordinateDestination->colony->id],['created_at', '>=', Carbon::now()->sub('2h')]])->orderBy('created_at','DESC')->get();
                    if($spyLast2Hours->count() > 0)
                    {
                        $nextSpy = Carbon::createFromFormat("Y-m-d H:i:s",$spyLast2Hours->first()->created_at)->add('2h');
                        $nextVacationString = Carbon::now()->diffForHumans($nextSpy,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('stargate.alreadySpied', ['time' => $nextVacationString], $this->player->lang);
                    }

                    $wraithProbe = Unit::where('slug', 'wraithProbe')->first();
                    $wraithProbeNumber = $this->player->activeColony->hasCraft($wraithProbe);
                    if(!$wraithProbeNumber)
                        return trans('generic.notEnoughResources', ['missingResources' => $wraithProbe->name.': 1'], $this->player->lang);
                    elseif($wraithProbeNumber == 0)
                        return trans('generic.notEnoughResources', ['missingResources' => $wraithProbe->name.': 1'], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $spyMessage = trans('stargate.spyConfirmation', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => $wraithProbe->name.': 1'], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($spyMessage)->then(function ($messageSent) use($sourceCoordinates,$destCoordinates,$wraithProbe){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });

                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector)  use($sourceCoordinates,$destCoordinates,$wraithProbe){
                            $messageReaction = $collector->first();
                            try{

                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    try
                                    {
                                        $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                                        if($raidCapability['result'] == false)
                                            $messageReaction->message->channel->sendMessage($raidCapability['message']);

                                        $this->player->activeColony->refresh();

                                        $current = Carbon::now();
                                        $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->coordinateDestination->colony->last_claim);
                                        if($current->diffInMinutes($lastClaim) > 720){
                                            $this->coordinateDestination->colony->checkColony();
                                            $this->coordinateDestination->load('colony');
                                        }

                                        $this->player->activeColony->E2PZ -= $this->travelCost;
                                        $this->player->activeColony->save();

                                        $wraithProbeExists = $this->player->activeColony->units->filter(function ($value){
                                            return $value->slug == 'wraithProbe';
                                        });
                                        if($wraithProbeExists->count() > 0)
                                        {
                                            $unitToUpdate = $wraithProbeExists->first();
                                            $unitToUpdate->pivot->number -= 1;
                                            $unitToUpdate->pivot->save();
                                        }

                                        $this->fleet = new Fleet;
                                        $this->fleet->mission = 'spy';
                                        $this->fleet->player_source_id = $this->player->id;
                                        $this->fleet->colony_source_id = $this->player->activeColony->id;
                                        $this->fleet->player_destination_id = $this->coordinateDestination->colony->player->id;
                                        $this->fleet->colony_destination_id = $this->coordinateDestination->colony->id;
                                        $this->fleet->departure_date = Carbon::now();
                                        $this->fleet->crew = 0;
                                        $this->fleet->capacity = 0;

                                        //Get arrivalDate
                                        $travelTime = $this->fleet->getFleetTime($this->player->activeColony->coordinates, $this->coordinateDestination, 50);
                                        $this->fleet->arrival_date = Carbon::now()->add($travelTime.'s');
                                        $this->fleet->save();

                                        $now = Carbon::now();
                                        $fleetDuration = $now->diffForHumans($this->fleet->arrival_date,[
                                            'parts' => 3,
                                            'short' => true, // short syntax as per current locale
                                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                        ]);

                                        $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                        $spyConfirmedMessage = trans('stargate.probeSpySending', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => $wraithProbe->name.': 1', 'fleetDuration' => $fleetDuration], $this->player->lang);

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                            ],
                                            //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpSending.gif'],
                                            "title" => "Stargate",
                                            "description" => $spyConfirmedMessage,
                                            'fields' => [
                                            ],
                                            'footer' => array(
                                                'text'  => 'Stargate',
                                            ),
                                        ];
                                        $newEmbed = $this->discord->factory(Embed::class,$embed);
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('stargate.spyCancelled', [], $this->player->lang)]);
                                    $messageReaction->message->addEmbed($newEmbed);
                                }

                                $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                            }
                            catch(\Exception $e)
                            {
                                echo $e->getMessage();
                            }
                        });
                    });
                }

                if(Str::startsWith('attack',$this->args[0]))
                {
                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang).' / !fleet attack [Coordinates] [Ship1] [Qty1]';

                    if(is_null($this->coordinateDestination->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    if(!$this->player->isRaidable($this->coordinateDestination->colony->player) && $this->coordinateDestination->colony->player->npc == 0)
                        return trans('stargate.weakOrStrong', [], $this->player->lang);

                    $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                    if($raidCapability['result'] == false)
                        return $raidCapability['message'];

                    $this->fleet->mission = 'attack';

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $now = Carbon::now();
                    $fleetDuration = $now->diffForHumans($this->fleet->arrival_date,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $baseMsg = trans('fleet.fleetAttackMessage', [ 'mission' => ucfirst($this->fleet->mission),
                                                                'coordinateDestination' => $destCoordinates,
                                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                                'planetSource' => $this->player->activeColony->name,
                                                                'coordinateSource' => $sourceCoordinates,
                                                                'fleet' => $fleetString,
                                                                'freightCapacity' => number_format($this->usedCapacity).' / '.number_format($this->fleet->capacity),
                                                                'crew' => number_format($this->fleet->crew),
                                                                'speed' => $this->fleetMaxSpeed,
                                                                'maxSpeed' => $this->fleetSpeed,
                                                                'fuel' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil($this->travelCost)),
                                                                'duration' => $fleetDuration,
                                                            ], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($baseMsg)->then(function ($messageSent){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });

                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    echo 'CONFIRMED';
                                    $this->player->activeColony->refresh();

                                    //CHECK SHIPS
                                    try{
                                        foreach($this->fleetShips as $fleetShip)
                                        {
                                            $shipCheck = $this->player->activeColony->hasShipById($fleetShip['id']);
                                            if(!($shipCheck && $shipCheck->pivot->number >= $fleetShip['qty']))
                                            {
                                                $qtyOwned = 0;
                                                if($shipCheck)
                                                {
                                                    $qtyOwned = $shipCheck->pivot->number;
                                                    $resource = $shipCheck->name;
                                                }
                                                else
                                                {
                                                    $ship = Ship::find($fleetShip['id']);
                                                    $resource = $ship->name;
                                                }

                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.number_format(ceil($fleetShip['qty']-$qtyOwned))], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                        }

                                        $this->fleet->save();
                                        //addShipToFleet
                                        foreach($this->fleetShips as $fleetShip)
                                        {
                                            $this->fleet->ships()->attach([$fleetShip['id'] => ['number' => $fleetShip['qty']]]);
                                            $shipCheck = $this->player->activeColony->hasShipById($fleetShip['id']);
                                            $shipCheck->pivot->number -= $fleetShip['qty'];
                                            $shipCheck->pivot->save();
                                            //$this->activeColony->ships()->updateExistingPivot($fleetShip['id'], array('number' => 1), false);
                                        }
                                        echo '<br/>dddddd';

                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo $e->getMessage();
                                    }
                                    echo '<br/>fffffff';
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    echo 'CANCELLED';
                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                }
                                $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                            }
                            catch(\Exception $e)
                            {
                                echo $e->getMessage();
                            }
                        });
                    });

                }

                /*
                if(Str::startsWith('colonize',$this->args[0]))
                {
                    if(!is_null($this->coordinateDestination->colony))
                        return trans('stargate.playerOwned', [], $this->player->lang);

                    if($this->player->activeColony->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.ceil(1000-$this->player->activeColony->military)], $this->player->lang);

                    $maxColonies = config('stargate.maxColonies');
                    $colonyMaxBonusList = $this->player->activeColony->artifacts->filter(function ($value){
                        return $value->bonus_category == 'ColonyMax';
                    });
                    foreach($colonyMaxBonusList as $colonyMaxBonus)
                    {
                        $maxColonies += $colonyMaxBonus->bonus_coef;
                    }

                    if($this->player->colonies->count() < $maxColonies)
                    {
                        $this->player->activeColony->military -= 1000;
                        $this->player->activeColony->E2PZ -= $this->travelCost;
                        $this->player->activeColony->save();
                        $destCoordinates = $this->coordinateDestination->humanCoordinates();
                        $this->player->addColony($this->coordinateDestination);

                        $embed = [
                            'author' => [
                                'name' => $this->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/colonize.gif'],
                            "title" => "Stargate",
                            "description" => trans('stargate.colonizeDone', ['destination' => $destCoordinates], $this->player->lang),
                            'fields' => [
                            ],
                            'footer' => array(
                                'text'  => 'Stargate',
                            ),
                        ];
                        $newEmbed = $this->discord->factory(Embed::class,$embed);
                        $this->message->channel->sendMessage('',false,$newEmbed);
                    }
                    else
                    {
                        return trans('stargate.toManyColonies', [], $this->player->lang);
                    }

                }
                */

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
        $galaxyDifference = abs($source->galaxy - $destination->galaxy);
        $systemDifference = abs($source->system - $destination->system);
        $planetDifference = abs($source->planet - $destination->planet);

        if($galaxyDifference > 0)
            return 200 + 150 * $galaxyDifference + 10 * $systemDifference;
        elseif($systemDifference > 1)
            return 2 + 1.5 * $systemDifference;
        else
            return 1 + 0.1 * $planetDifference;
    }

    public function canAttack($colonySource,$colonyDest)
    {
        $now = Carbon::now();

        $atkNbr = GateFight::Where([['active',true],['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id]])->count();
        if($atkNbr > 0)
        {
            $atkColony = GateFight::Where([['active', true],['colony_id_source',$colonySource->id],['colony_id_dest',$colonyDest->id]])->count();
            if($atkNbr >= 6 || $atkColony >= 2)
            {
                $firstAttack = GateFight::Where([['active',true],['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id]])->orderBy('created_at', 'asc')->first();
                $firstAttackTime = Carbon::createFromFormat("Y-m-d H:i:s",$firstAttack->created_at);
                $timeUntilAttack = $now->diffForHumans($firstAttackTime->addHours(72),[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
                return array('result' => false, 'message' =>trans('stargate.AttackLimit', ['time' => $timeUntilAttack], $this->player->lang));
            }
        }

        $last24h = GateFight::Where([['active', true],['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('24h')]])->get();
        if($last24h->count() >= 3)
        {
            $now = Carbon::now();
            $firstFight = Carbon::createFromFormat("Y-m-d H:i:s",$last24h[0]->created_at);
            $timeUntilAttack = $now->diffForHumans($firstFight->addHours(24),[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);
            return array('result' => false, 'message' => trans('stargate.AttackLimit', ['time' => $timeUntilAttack], $this->player->lang));
        }

        $activeFights = GateFight::Where([['active', true],['colony_id_source',$colonySource->id],['colony_id_dest',$colonyDest->id]])->orderBy('created_at', 'asc')->get();
        if($activeFights->count() > 0)
        {
            $now = Carbon::now();
            $lastFight = Carbon::createFromFormat("Y-m-d H:i:s",$activeFights[$activeFights->count()-1]->created_at);
            if($lastFight->diffInHours($now) < 24){
                $timeUntilAttack = $now->diffForHumans($lastFight->addHours(24),[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
                return array('result' => false, 'message' => trans('stargate.AttackLimit', ['time' => $timeUntilAttack], $this->player->lang));
            }
        }
        return array('result' => true);
    }
}
