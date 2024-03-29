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
use App\Pact;
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
    public $fleetUnits;
    public $transportString;
    public $fleetMaxSpeed;
    public $fleetSpeed;
    public $usedCapacity;
    public $fleetSpeedBonus;
    public $fleetConsumptionBonus;
    public $fleetHistory;
    public $fightPages;
    public $boosted;
    public $boostCost;

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
                $this->fleetSpeedBonus = $this->player->getShipSpeedBonus();
                $this->fleetConsumptionBonus = $this->player->getShipConsumptionBonus();

                $comTechnology = Technology::find(1); // Info et com
                $currentComTechLvl = $this->player->hasTechnology($comTechnology);
                if(!$currentComTechLvl)
                {
                    return trans('fleet.missingComTech', [], $this->player->lang);
                }

                if(isset($this->args[0]) && Str::startsWith('history',$this->args[0]))
                {
                    if(isset($this->args[1]) && (int)$this->args[1] > 0)
                    {
                        $fleet = Fleet::find((int)$this->args[1]);
                        if(!is_null($fleet) && (!$fleet->ended || $fleet->mission == 'attack') && (config('stargate.ownerId') == $this->player->id || $fleet->player_source_id == $this->player->id || $fleet->player_destination_id == $this->player->id))
                        {
                            //Specific history
                            switch($fleet->mission)
                            {
                                case 'attack':
                                    $fleetArrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleet->arrival_date);
                                    $now = Carbon::now();
                                    $arrivalDate = $now->diffForHumans($fleetArrivalDate,[
                                        'parts' => 3,
                                        'short' => true, // short syntax as per current locale
                                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                    ]);

                                    if(!is_null($fleet->gateFight) && ($fleet->ended || $fleet->returning))
                                        $this->displayFight($fleet);
                                    elseif(
                                        $this->player->id == $fleet->sourcePlayer->id && !$fleet->ended
                                        ||
                                        ($this->player->id == $fleet->destinationPlayer->id && !is_null($fleet->gateFight))
                                    )
                                    {
                                        $fleetArrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleet->arrival_date);
                                        $now = Carbon::now();
                                        $arrivalDate = $now->diffForHumans($fleetArrivalDate,[
                                            'parts' => 3,
                                            'short' => true, // short syntax as per current locale
                                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                        ]);

                                        if($this->player->id == $fleet->sourcePlayer->id)
                                        {
                                            if($fleet->returning)
                                                $status = trans('fleet.returningStatus', [], $this->player->lang);
                                            else
                                                $status = trans('fleet.ongoingStatus', [], $this->player->lang);

                                            $fleetDetail = trans('fleet.fleetDetailOwned', [
                                                'source' => $fleet->sourceColony->name.' ['.$fleet->sourceColony->coordinates->humanCoordinates().'] ('.$fleet->sourcePlayer->user_name.')',
                                                'destination' => $fleet->destinationColony->name.' ['.$fleet->destinationColony->coordinates->humanCoordinates().'] ('.$fleet->destinationPlayer->user_name.')',
                                                'mission' => $fleet->mission,
                                                'status' => $status,
                                                'fleet' => $fleet->shipsToString(),
                                                'freightCapacity' => $fleet->capacity,
                                                'resources' => $fleet->resourcesToString($this->player->lang),
                                                'duration' => $arrivalDate
                                            ], $this->player->lang);
                                        }
                                        else
                                        {
                                            $spy = Technology::where('slug', 'spy')->first();
                                            $counterSpy = Technology::where('slug', 'counterspy')->first();

                                            $spyLvl = $fleet->sourcePlayer->hasTechnology($spy);
                                            $counterSpyLvl = $fleet->destinationPlayer->hasTechnology($counterSpy);

                                            if(!$spyLvl)
                                                $spyLvl = 0;
                                            if(!$counterSpyLvl)
                                                $counterSpyLvl = 0;

                                            $fleetString = "\nUNKNOWN";
                                            if($spyLvl > $counterSpyLvl && ($spyLvl-$counterSpyLvl) >= 1)
                                                $fleetString = $fleet->shipsToString();
                                            elseif($spyLvl > $counterSpyLvl && ($spyLvl-$counterSpyLvl) >= 2)
                                                $fleetString = $fleet->shipsToString(true,$this->player->lang);

                                            $fleetDetail = trans('fleet.fleetDetailArriving', [
                                                'source' => $fleet->sourceColony->name.' ['.$fleet->sourceColony->coordinates->humanCoordinates().'] ('.$fleet->sourcePlayer->user_name.')',
                                                'destination' => $fleet->destinationColony->name.' ['.$fleet->destinationColony->coordinates->humanCoordinates().'] ('.$fleet->destinationPlayer->user_name.')',
                                                'mission' => $fleet->mission,
                                                'fleet' => $fleetString,
                                                'duration' => $arrivalDate
                                            ], $this->player->lang);
                                        }
                                        return $fleetDetail;
                                    }
                                    else
                                        return trans('fleet.unknownFleet', [], $this->player->lang);
                                break;
                                case 'base':
                                case 'transport':
                                case 'scavenge':
                                case 'spy':
                                    if(($fleet->returning || $fleet->mission == 'scavenge') && $this->player->id != $fleet->sourcePlayer->id)
                                        return trans('fleet.unknownFleet', [], $this->player->lang);
                                    else
                                    {
                                        $fleetArrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleet->arrival_date);
                                        $now = Carbon::now();
                                        $arrivalDate = $now->diffForHumans($fleetArrivalDate,[
                                            'parts' => 3,
                                            'short' => true, // short syntax as per current locale
                                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                        ]);
                                        if($fleet->returning)
                                            $status = trans('fleet.returningStatus', [], $this->player->lang);
                                        else
                                            $status = trans('fleet.ongoingStatus', [], $this->player->lang);

                                        if($this->player->id == $fleet->sourcePlayer->id)
                                        {
                                            if(in_array($fleet->mission,array('spy','scavenge')))
                                            {
                                                $fleetString = $fleet->resourcesToString($this->player->lang,true,false);
                                                $resString = $fleet->resourcesToString($this->player->lang,false,true);
                                            }
                                            else
                                            {
                                                $fleetString = $fleet->shipsToString();
                                                $resString = $fleet->resourcesToString($this->player->lang);
                                            }

                                            $fleetDetail = trans('fleet.fleetDetailOwned', [
                                                'source' => $fleet->sourceColony->name.' ['.$fleet->sourceColony->coordinates->humanCoordinates().'] ('.$fleet->sourcePlayer->user_name.')',
                                                'destination' => $fleet->destinationColony->name.' ['.$fleet->destinationColony->coordinates->humanCoordinates().'] ('.$fleet->destinationPlayer->user_name.')',
                                                'mission' => $fleet->mission,
                                                'status' => $status,
                                                'fleet' => $fleetString,
                                                'freightCapacity' => number_format($fleet->capacity),
                                                'resources' => $resString,
                                                'duration' => $arrivalDate
                                            ], $this->player->lang);

                                        }
                                        else
                                        {
                                            if(in_array($fleet->mission,array('spy')))
                                                $fleetString = $fleet->resourcesToString($this->player->lang,true,false);
                                            else
                                            {
                                                $spy = Technology::where('slug', 'spy')->first();
                                                $counterSpy = Technology::where('slug', 'counterspy')->first();

                                                $spyLvl = $fleet->sourcePlayer->hasTechnology($spy);
                                                $counterSpyLvl = $fleet->destinationPlayer->hasTechnology($counterSpy);

                                                if(!$spyLvl)
                                                    $spyLvl = 0;
                                                if(!$counterSpyLvl)
                                                    $counterSpyLvl = 0;

                                                $fleetString = "UNKNOWN\n";
                                                if($spyLvl > $counterSpyLvl && ($spyLvl-$counterSpyLvl) >= 1)
                                                    $fleetString = $fleet->shipsToString();
                                                elseif($spyLvl > $counterSpyLvl && ($spyLvl-$counterSpyLvl) >= 2)
                                                    $fleetString = $fleet->shipsToString(true,$this->player->lang);
                                            }

                                            $fleetDetail = trans('fleet.fleetDetailArriving', [
                                                'source' => $fleet->sourceColony->name.' ['.$fleet->sourceColony->coordinates->humanCoordinates().'] ('.$fleet->sourcePlayer->user_name.')',
                                                'destination' => $fleet->destinationColony->name.' ['.$fleet->destinationColony->coordinates->humanCoordinates().'] ('.$fleet->destinationPlayer->user_name.')',
                                                'mission' => $fleet->mission,
                                                'status' => $status,
                                                'fleet' => $fleetString,
                                                'duration' => $arrivalDate
                                            ], $this->player->lang);
                                        }
                                        return $fleetDetail;
                                    }
                                break;
                                default:
                                    return trans('fleet.unknownFleet', [], $this->player->lang);
                                break;
                            }
                            return;
                        }
                        else
                            return trans('fleet.unknownFleet', [], $this->player->lang);
                    }

                    $fleetHistory = Fleet::where([['mission', '!=', 'attack'],['ended', false],['player_source_id', $this->player->id]])
                                        ->orWhere([['mission', '!=', 'attack'],['mission', '!=' ,'scavenge'] ,['returning', false],['ended', false],['player_destination_id', $this->player->id]])
                                        ->orWhere([['mission', 'attack'],['player_source_id', $this->player->id]])
                                        ->orWhere([['mission', 'attack'],['player_destination_id', $this->player->id]])
                                        ->orderBy('created_at','DESC')
                                        ->take(100)->get();
                    $this->fleetHistory = $fleetHistory->filter(function ($value){
                        return $value->mission != 'attack'
                         || ($value->mission == 'attack' && $value->ended && !is_null($value->gateFight))
                         || ($value->mission == 'attack' && !$value->ended && $value->player_source_id != $this->player->id)
                         || ($value->mission == 'attack' && !$value->ended && (!$value->returning || !is_null($value->gateFight)) && $value->player_destination_id == $this->player->id);
                    });

                    if($this->fleetHistory->count() == 0)
                    {
                        return trans('fleet.emptyHistory', [], $this->player->lang);
                    }

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->fleetHistory->count()/10);
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
                    return;
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
                            $sourceColony = $activeFleet->destinationColony;
                            $destinationColony = $activeFleet->sourceColony;
                        }
                        else
                        {
                            $fleetStatus = trans('fleet.ongoingStatus', [], $this->player->lang);
                            $sourceColony = $activeFleet->sourceColony;
                            $destinationColony = $activeFleet->destinationColony;
                        }

                        $arrivalDateCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$activeFleet->arrival_date);
                        $arrivalDate = $now->diffForHumans($arrivalDateCarbon,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        $shipType = trans('generic.ships', [], $this->player->lang);
                        if(in_array($activeFleet->mission, array('scavenge','spy')))
                        {
                            $shipCount = $activeFleet->unitCount();
                            if($activeFleet->mission == 'scavenge')
                                $shipType = trans('generic.scavengers', [], $this->player->lang);
                            else
                                $shipType = trans('generic.probe', [], $this->player->lang);
                        }
                        else
                            $shipCount = $activeFleet->shipCount();

                        $activeFleetsString .= $arrivalDate.' - '.trans('fleet.activeFleet', [
                                                                        'mission' => ucfirst($activeFleet->mission),
                                                                        'id' => $activeFleet->id,
                                                                        'status' => $fleetStatus,
                                                                        'shipCount' => $shipCount,
                                                                        'shipType' => $shipType,
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
                        $sourceColony = $incomingFleet->sourceColony;
                        $destinationColony = $incomingFleet->destinationColony;

                        $arrivalDateCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$incomingFleet->arrival_date);
                        $arrivalDate = $now->diffForHumans($arrivalDateCarbon,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        $shipType = trans('generic.ships', [], $this->player->lang);
                        if($incomingFleet->mission == 'spy')
                        {
                            $shipCount = 1;
                            $shipType = trans('generic.probe', [], $this->player->lang);
                        }
                        else
                            $shipCount = $incomingFleet->shipCount();

                        $incomingFleetString .= $arrivalDate.' - '.trans('fleet.incomingFleet', [
                                                                        'mission' => $incomingFleet->mission,
                                                                        'shipCount' => $shipCount,
                                                                        'shipType' => $shipType,
                                                                        'colonySource' => $sourceColony->name,
                                                                        'coordinatesSource' => $sourceColony->coordinates->humanCoordinates(),
                                                                        'colonyDest' => $destinationColony->name,
                                                                        'coordinatesDest' => $destinationColony->coordinates->humanCoordinates(),
                                                                        ], $this->player->lang)."\n";
                    } //dest
                    if(empty($incomingFleetString))
                        $incomingFleetString = trans('fleet.noIncomingFleet', [], $this->player->lang)."\n";

                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/fleet.jpg'],
                        "title" => "Fleet",
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
                elseif(!Str::startsWith('base',$this->args[0]) && !Str::startsWith('transport',$this->args[0]) && !Str::startsWith('attack',$this->args[0])
                && !Str::startsWith('spy',$this->args[0]) && !Str::startsWith('order',$this->args[0]) && !Str::startsWith('scavenge',$this->args[0]))
                {
                    return trans('fleet.wrongParameter', [], $this->player->lang);
                }

                /*if(Str::startsWith('scavenge',$this->args[0]))
                    return 'Not yet implemented';*/

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

                        if($fleetControl->mission == 'attack')
                            GateFight::where('fleet_id',$fleetControl->id)->delete();

                        $now = Carbon::now();
                        $fleetDuration = $now->diffForHumans($fleetControl->arrival_date,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        return trans('fleet.fleetReturning', ['duration' => $fleetDuration,
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
                    elseif(!(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0])))
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

                if(is_null($this->coordinateDestination->colony))
                    return trans('stargate.neverExploredWorld', [], $this->player->lang);

                if(!is_null($this->coordinateDestination->colony) && $this->player->user_id != config('stargate.ownerId'))
                {
                    if(!is_null($this->coordinateDestination->colony->player->vacation) && !Str::startsWith('scavenge',$this->args[0]))
                        return trans('profile.playerVacation', [], $this->player->lang);
                }

                //&& $this->player->user_id != config('stargate.ownerId')
                if( !(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0]) || Str::startsWith('scavenge',$this->args[0])) && !is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id == $this->player->id && $this->player->id != 1 )
                    return trans('stargate.samePlayerAction', [], $this->player->lang);

                if(Str::startsWith('base',$this->args[0]) && !is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id != $this->player->id)
                    return trans('stargate.notAColonyOfYour', [], $this->player->lang);

                if(!Str::startsWith('scavenge',$this->args[0]) && $this->coordinateDestination->id == $this->player->activeColony->coordinates->id && $this->player->user_id != config('stargate.ownerId'))
                    return trans('stargate.sameColony', [], $this->player->lang);

                //Base Fuel Consumption
                $this->baseTravelCost = $this->getConsumption($this->player->activeColony->coordinates,$this->coordinateDestination);
                $this->travelCost = $this->baseTravelCost;

                $fleetString = '';
                if(!Str::startsWith('spy',$this->args[0]) && !Str::startsWith('scavenge',$this->args[0]))
                {
                    if(Str::startsWith('transport',$this->args[0]) && $this->coordinateDestination->colony->player->id != $this->player->id)
                    {
                        $pactExists = Pact::Where([['player_1_id', $this->player->id], ['player_2_id', $this->coordinateDestination->colony->player->id]])->orWhere([['player_2_id', $this->player->id], ['player_1_id', $this->coordinateDestination->colony->player->id]])->get()->first();
                        if(is_null($pactExists) && $this->player->user_id != config('stargate.ownerId'))
                            return trans('trade.noPactWithThisPlayer', [] , $this->player->lang);
                    }

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

                    $this->boosted = false;
                    $availableResources = config('stargate.resources');
                    $availableResources[] = 'E2PZ';
                    $availableResources[] = 'military';
                    $this->transportString = '';
                    $this->usedCapacity = 0;
                    $this->fleetMaxSpeed = 1000;
                    $this->fleetSpeed = 100;
                    $this->fleetShips = array();
                    $this->fleetUnits = array();

                    $withScav = false;

                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if(is_numeric($this->args[$cptRes+1]) && $this->args[$cptRes+1] > 0)
                                $qty = $this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            if(Str::startsWith('speed',$this->args[$cptRes]))
                            {
                                if($qty >= 10 && $qty <= 100)
                                    $this->fleetSpeed = (int)$qty;
                                else
                                    $this->fleetSpeed = 100;
                            }
                            else
                            {
                                $resourceName = $this->args[$cptRes];

                                $shipExists = $this->player->ships->filter(function ($value) use($resourceName){
                                    return Str::startsWith($value->slug, $resourceName);
                                });
                                if($shipExists->count() > 0)
                                {
                                    $qty = (int)$qty;
                                    $playerShip = $shipExists->first();

                                    $alreadyFound = array_search($playerShip->id, array_column($this->fleetShips, 'id'));
                                    if($alreadyFound === false)
                                    {
                                        $fleetString .= $playerShip->name.': '.number_format($qty)."\n";

                                        $colonyHasShip = $this->player->activeColony->ships->filter(function ($value) use($playerShip){
                                            return $value->id == $playerShip->id;
                                        });
                                        if($colonyHasShip->count() > 0)
                                            $owned = $colonyHasShip->first()->pivot->number;
                                        else
                                            $owned = 0;

                                        //Check si possède
                                        if($owned < $qty)
                                            return trans('generic.notEnoughResources', ['missingResources' => $playerShip->name.': '.number_format($qty - $owned)], $this->player->lang);

                                        $this->fleet->crew += $playerShip->crew*$qty;
                                        $this->fleet->capacity += $playerShip->capacity*$qty;
                                        $shipSpeed = $playerShip->speed * $this->fleetSpeedBonus;
                                        if($this->fleetMaxSpeed > $shipSpeed)
                                            $this->fleetMaxSpeed = round($shipSpeed,2);

                                        $this->travelCost += $this->baseTravelCost * $qty * $playerShip->required_blueprint;
                                        $this->fleetShips[] = array('id' => $playerShip->id,'qty' => $qty);
                                    }
                                }
                                else
                                {
                                    if(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0]))
                                    {
                                        $ressFound = $bypass = false;
                                        if(Str::startsWith('e2pz',$resourceName) || Str::startsWith('zpm',$resourceName) || Str::startsWith('ZPM',$resourceName))
                                            $resourceName = 'E2PZ';

                                        if($resourceName == 'E2PZ')
                                            $qty = round($qty,2);
                                        else
                                            $qty = (int)$qty;

                                        foreach($availableResources as $availableResource)
                                        {
                                            if((Str::startsWith($availableResource,$resourceName) || Str::startsWith($availableResource,strtoupper($resourceName))) && !strstr($this->transportString,$availableResource))
                                            {
                                                $ressFound = true;
                                                $resourceName = $availableResource;
                                                $this->fleet->$resourceName = $qty;
                                                $this->usedCapacity += $qty;

                                                if($resourceName != 'E2PZ' && $resourceName != 'military' && $this->coordinateDestination->colony->{'storage_'.$resourceName} < ($this->fleet->$resourceName))
                                                    return trans('stargate.tradeStorageTooLow', ['resource' => config('stargate.emotes.'.strtolower($resourceName))." ".ucfirst($resourceName)], $this->player->lang);

                                                $resQty = number_format($qty);
                                                if($resourceName == 'E2PZ')
                                                    $resQty = number_format($qty,2);

                                                $this->transportString .= config('stargate.emotes.'.strtolower($resourceName))." ".ucfirst($resourceName).': '.$resQty."\n";
                                            }
                                            elseif(strstr($this->transportString,$availableResource))
                                                $bypass = true;
                                        }
                                        if(!$ressFound && !$bypass)
                                        {
                                            $unit = Unit::Where('slug', 'LIKE', $resourceName.'%')->first();
                                            if(is_null($unit))
                                                return trans('stargate.unknownResource', ['resource' => $resourceName], $this->player->lang);
                                            else
                                            {
                                                $alreadyFound = array_search($unit->id, array_column($this->fleetUnits, 'id'));
                                                if($alreadyFound === false)
                                                {
                                                    $this->fleetUnits[] = array('id' => $unit->id,'qty' => $qty);
                                                    $this->transportString .= trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)."\n";
                                                    if($unit->capacity > 0 && !strstr($unit->slug,'scav'))
                                                        $this->usedCapacity += $unit->capacity * $qty;
                                                    if(strstr($unit->slug,'scav'))
                                                    {
                                                        $withScav = true;
                                                        $unitSpeed = $unit->speed * $this->fleetSpeedBonus;
                                                        if($this->fleetMaxSpeed > $unitSpeed)
                                                            $this->fleetMaxSpeed = round($unitSpeed,2);
                                                        $this->travelCost += floor($this->baseTravelCost * $qty);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        elseif(Str::startsWith('boost',$this->args[$cptRes]) && (Str::startsWith('attack',$this->args[0]) || Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0])))
                        {
                            $this->boosted = true;
                            $this->fleet->boosted = true;
                        }
                        else
                            return trans('fleet.wrongParameter', [], $this->player->lang);
                    }

                    if($this->boosted){
                        $this->boostCost = array_sum(array_column($this->fleetShips, 'qty'))*0.5;

                        if($this->player->activeColony->E2PZ < $this->boostCost)
                            return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.number_format($this->boostCost - $this->player->activeColony->E2PZ,2)], $this->player->lang);
                    }


                    if(empty($this->transportString))
                    {
                        if(Str::startsWith('transport',$this->args[0]))
                            return trans('fleet.noResourceSeleted', [], $this->player->lang);
                        else
                            $this->transportString = "/\n";
                    }

                    if(empty($this->fleetShips) && !$withScav)
                        return trans('fleet.noShipSelected', [], $this->player->lang);
                    elseif(empty($this->fleetShips) && $withScav)
                    {
                        foreach($this->fleetUnits as $fleetUnit)
                        {
                            if(!in_array($fleetUnit['id'],array(5,6,7,8)))
                                return trans('fleet.noShipSelected', [], $this->player->lang);
                        }
                    }

                    //check crew capacity
                    if($this->fleet->crew > $this->player->activeColony->military)
                        return trans('generic.notEnoughResources', ['missingResources' => trans('shipyard.crew', ['crew' => number_format(ceil($this->fleet->crew - $this->player->activeColony->military))], $this->player->lang)], $this->player->lang);

                    if(($this->fleet->crew + $this->fleet->military) > $this->player->activeColony->military)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang). ': '. number_format(ceil(($this->fleet->crew + $this->fleet->military) - $this->player->activeColony->military))], $this->player->lang);

                    //check Speed
                    if($this->boosted)
                        $this->fleetSpeed *= 1.2;
                    $this->fleetMaxSpeed = $this->fleetMaxSpeed * ($this->fleetSpeed/100);

                    $this->travelCost = floor($this->travelCost * ($this->fleetSpeed/100));

                    $this->usedCapacity += $this->travelCost;
                    //check fret capacity

                    if($this->fleet->capacity < $this->usedCapacity && !(empty($this->fleetShips) && $withScav))
                        return trans('fleet.notEnoughCapacity', ['missingCapacity' => number_format(($this->usedCapacity) - $this->fleet->capacity)], $this->player->lang);

                    //check Carburant
                    if(($this->fleet->naqahdah + $this->travelCost) > $this->player->activeColony->naqahdah)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil(($this->fleet->naqahdah + $this->travelCost) - $this->player->activeColony->naqahdah))], $this->player->lang);

                    //Get arrivalDate
                    $travelTime = $this->fleet->getFleetTime($this->player->activeColony->coordinates, $this->coordinateDestination, $this->fleetMaxSpeed);
                    $this->fleet->arrival_date = Carbon::now()->add($travelTime.'s');
                }

                if(Str::startsWith('base',$this->args[0]) || Str::startsWith('transport',$this->args[0]))
                {
                    if(Str::startsWith('transport',$this->args[0])){

                        if($this->player->trade_ban)
                            return trans('stargate.trade_ban', [], $this->player->lang);
                        elseif($this->coordinateDestination->colony->player->ban)
                            return trans('stargate.playerTradeBan', [], $this->player->lang);
                        elseif($this->player->user_id != config('stargate.ownerId') && $this->coordinateDestination->colony->player->id != $this->player->id)
                        {
                            $activeTradeCheck = Trade::where([["player_id_source", $this->player->id], ["player_id_dest", '!=', $this->coordinateDestination->colony->player->id], ["active", true]])
                                                ->orWhere([["player_id_dest", $this->player->id], ["player_id_source", '!=', $this->coordinateDestination->colony->player->id], ["active", true]])->count();

                            if($activeTradeCheck > 0)
                                return trans('trade.youAlreadyHaveActiveTrade', [], $this->player->lang);
                            else
                            {
                                $playerActiveTradeCheck = Trade::where([["player_id_source", '!=', $this->player->id], ["player_id_dest", $this->coordinateDestination->colony->player->id], ["active", true]])
                                ->orWhere([["player_id_dest", '!=', $this->player->id], ["player_id_source", $this->coordinateDestination->colony->player->id], ["active", true]])->count();

                                if($playerActiveTradeCheck > 0)
                                    return trans('trade.playerHasActiveTrade', [], $this->player->lang);
                            }
                        }

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

                    if(empty($this->fleetShips) && $withScav)
                        $this->usedCapacity = 0;

                    $boostedString = '/';
                    if($this->boosted)
                    {
                        $boostedString = config('stargate.emotes.shield').' +15% '.config('stargate.emotes.speed').' +20% ('.config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).' '.number_format($this->boostCost,2).')';
                    }

                    $baseMsg = trans('fleet.fleetMessage', [ 'mission' => ucfirst($this->fleet->mission),
                                                                'coordinateDestination' => $destCoordinates,
                                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                                'planetSource' => $this->player->activeColony->name,
                                                                'coordinateSource' => $sourceCoordinates,
                                                                'fleet' => $fleetString,
                                                                'freightCapacity' => number_format($this->usedCapacity).' / '.number_format($this->fleet->capacity),
                                                                'resources' => $this->transportString,
                                                                'crew' => number_format($this->fleet->crew),
                                                                'choosedSpeed' => $this->fleetMaxSpeed,
                                                                'maxSpeed' => $this->fleetSpeed,
                                                                'boosted' => $boostedString,
                                                                'fuel' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(floor($this->travelCost)),
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
                        $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector){
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

                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => ucfirst($resource).': '.number_format(ceil($fleetShip['qty']-$qtyOwned))], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                        }

                                        //CHECK Units
                                        foreach($this->fleetUnits as $fleetUnit)
                                        {
                                            $unitCheck = $this->player->activeColony->hasUnitById($fleetUnit['id']);
                                            if(!($unitCheck && $unitCheck->pivot->number >= $fleetUnit['qty']))
                                            {
                                                $qtyOwned = 0;
                                                if($unitCheck)
                                                {
                                                    $qtyOwned = $unitCheck->pivot->number;
                                                    $resource = trans('craft.'.$unitCheck->slug.'.name', [], $this->player->lang);
                                                }
                                                else
                                                {
                                                    $unit = Unit::find($fleetUnit['id']);
                                                    $resource = trans('craft.'.$unit->slug.'.name', [], $this->player->lang);
                                                }

                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => $resource.': '.number_format(ceil($fleetUnit['qty']-$qtyOwned))], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                        }

                                        //CHECK RESOURCES
                                        foreach($availableResources as $resource)
                                        {
                                            $qtyNeeded = $this->fleet->$resource;
                                            if($resource == 'naqahdah')
                                                $qtyNeeded += $this->travelCost;
                                            if($this->boosted && $resource == 'E2PZ')
                                                $qtyNeeded += $this->boostCost;

                                            if($qtyNeeded > 0)
                                            {
                                                if($this->player->activeColony->$resource >= $qtyNeeded)
                                                    $this->player->activeColony->$resource -= $qtyNeeded;
                                                else
                                                {
                                                    $resQty = number_format(ceil($qtyNeeded-$this->player->activeColony->$resource));
                                                    if($resource == 'E2PZ')
                                                        $resQty = number_format(($qtyNeeded-$this->player->activeColony->$resource),2);

                                                    $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.$resQty], $this->player->lang));
                                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                    return;
                                                }
                                            }
                                        }

                                        if($this->player->activeColony->military < $this->fleet->crew)
                                        {
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".ucfirst($resource).': '.number_format($this->fleet->crew-$this->player->activeColony->military)], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }

                                        $this->player->activeColony->military -= $this->fleet->crew;
                                        $this->player->activeColony->save();
                                        $this->fleet->save();
                                        //addShipToFleet
                                        foreach($this->fleetShips as $fleetShip)
                                        {
                                            $this->fleet->ships()->attach([$fleetShip['id'] => ['number' => $fleetShip['qty']]]);
                                            $shipCheck = $this->player->activeColony->hasShipById($fleetShip['id']);
                                            $shipCheck->pivot->number -= $fleetShip['qty'];
                                            if($shipCheck->pivot->number <= 0)
                                                $this->player->activeColony->ships()->detach($shipCheck->id);
                                            else
                                                $shipCheck->pivot->save();
                                        }
                                        //addUnitsToFleet
                                        foreach($this->fleetUnits as $fleetUnit)
                                        {
                                            $this->fleet->units()->attach([$fleetUnit['id'] => ['number' => $fleetUnit['qty']]]);
                                            $unitCheck = $this->player->activeColony->hasUnitById($fleetUnit['id']);
                                            $unitCheck->pivot->number -= $fleetUnit['qty'];

                                            if($unitCheck->pivot->number <= 0)
                                                $this->player->activeColony->units()->detach($unitCheck->id);
                                            else
                                                $unitCheck->pivot->save();
                                            //$this->activeColony->ships()->updateExistingPivot($fleetShip['id'], array('number' => 1), false);
                                        }

                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
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
                                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                            }
                        });
                    });

                    return;
                }

                if(Str::startsWith('spy',$this->args[0]))
                {
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
                    if(!$wraithProbeNumber || $wraithProbeNumber == 0)
                        return trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$wraithProbe->slug.'.name', [], $this->player->lang).': 1'], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $spyMessage = trans('stargate.spyConfirmation', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => trans('craft.'.$wraithProbe->slug.'.name', [], $this->player->lang).': 1'], $this->player->lang);

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
                        $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector)  use($sourceCoordinates,$destCoordinates,$wraithProbe){
                            $messageReaction = $collector->first();
                            try{

                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    try
                                    {
                                        $this->player->activeColony->refresh();

                                        $current = Carbon::now();
                                        $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->coordinateDestination->colony->last_claim);
                                        if($current->diffInMinutes($lastClaim) > 720){
                                            $this->coordinateDestination->colony->checkColony();
                                            $this->coordinateDestination->load('colony');
                                        }

                                        if($this->player->activeColony->naqahdah >= $this->travelCost)
                                            $this->player->activeColony->naqahdah -= $this->travelCost;
                                        else
                                        {
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil($this->travelCost-$this->player->activeColony->naqahdah))], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }

                                        $wraithProbe = Unit::where('slug', 'wraithProbe')->first();
                                        $wraithProbeNumber = $this->player->activeColony->hasCraft($wraithProbe);
                                        if(!$wraithProbeNumber || $wraithProbeNumber == 0)
                                        {
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$wraithProbe->slug.'.name', [], $this->player->lang).': 1'], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }

                                        $this->player->activeColony->save();

                                        $wraithProbeExists = $this->player->activeColony->units->filter(function ($value) use($wraithProbe){
                                            return $value->id == $wraithProbe->id;
                                        });
                                        if($wraithProbeExists->count() > 0)
                                        {
                                            $unitToUpdate = $wraithProbeExists->first();
                                            $unitToUpdate->pivot->number -= 1;
                                            if($unitToUpdate->pivot->number <= 0)
                                                $this->player->activeColony->units()->detach($unitToUpdate->id);
                                            else
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

                                        $this->fleet->units()->attach([$unitToUpdate->id => ['number' => 1]]);

                                        $now = Carbon::now();
                                        $fleetDuration = $now->diffForHumans($this->fleet->arrival_date,[
                                            'parts' => 3,
                                            'short' => true, // short syntax as per current locale
                                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                        ]);

                                        $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                        $spyConfirmedMessage = trans('stargate.probeSpySending', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'player' => $this->coordinateDestination->colony->player->user_name, 'consumption' => trans('craft.'.$wraithProbe->slug.'.name', [], $this->player->lang).': 1', 'fleetDuration' => $fleetDuration], $this->player->lang);

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                            ],
                                            //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpSending.gif'],
                                            "title" => "Fleet",
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
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('stargate.missionCancelled', [], $this->player->lang)]);
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
                }

                if(Str::startsWith('scavenge',$this->args[0]))
                {
                    ///FLEET CONSTITUTION
                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang).' / !fleet scavenge [Coordinates] [Scavengers] [Quantity]';

                    $this->fleet = new Fleet;
                    $this->fleet->mission = 'scavenge';
                    $this->fleet->player_source_id = $this->player->id;
                    $this->fleet->colony_source_id = $this->player->activeColony->id;
                    $this->fleet->player_destination_id = $this->coordinateDestination->colony->player->id;
                    $this->fleet->colony_destination_id = $this->coordinateDestination->colony->id;

                    $this->fleet->departure_date = Carbon::now();
                    $this->fleetMaxSpeed = 100;
                    $this->fleetSpeed = 100;
                    $this->fleetUnits = array();

                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if(is_numeric($this->args[$cptRes+1]) && $this->args[$cptRes+1] > 0)
                                $qty = (int)$this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            if(Str::startsWith('speed',$this->args[$cptRes]))
                            {
                                if($qty >= 10 && $qty <= 100)
                                    $this->fleetSpeed = (int)$qty;
                                else
                                    $this->fleetSpeed = 100;
                            }
                            else
                            {
                                $resourceName = $this->args[$cptRes];
                                $unit = Unit::Where([['slug', 'LIKE', $resourceName.'%'],['type','Scavenger']])->first();
                                if(is_null($unit))
                                    return trans('stargate.unknownResource', ['resource' => $resourceName], $this->player->lang);
                                else
                                {
                                    $alreadyFound = array_search($unit->id, array_column($this->fleetUnits, 'id'));
                                    if($alreadyFound === false)
                                    {
                                        $unitSpeed = $unit->speed * $this->fleetSpeedBonus;
                                        if($this->fleetMaxSpeed > $unitSpeed)
                                            $this->fleetMaxSpeed = round($unitSpeed,2);

                                        $this->travelCost += floor($this->baseTravelCost * $qty);

                                        $this->fleetUnits[] = array('id' => $unit->id,'qty' => $qty);
                                        $fleetString .= trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)."\n";
                                        if($unit->capacity > 0)
                                            $this->usedCapacity += $unit->capacity;
                                    }

                                }
                            }
                        }
                        else
                            return trans('fleet.wrongParameter', [], $this->player->lang);
                    }

                    if(empty($this->fleetUnits))
                        return trans('fleet.noScavengerSelected', [], $this->player->lang);

                    //check Speed
                    $this->fleetMaxSpeed = $this->fleetMaxSpeed * ($this->fleetSpeed/100);
                    $this->travelCost = floor($this->travelCost * ($this->fleetSpeed/100));

                    //check Carburant
                    if(($this->fleet->naqahdah + $this->travelCost) > $this->player->activeColony->naqahdah)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil(($this->fleet->naqahdah + $this->travelCost) - $this->player->activeColony->naqahdah))], $this->player->lang);

                    //Get arrivalDate
                    $travelTime = $this->fleet->getFleetTime($this->player->activeColony->coordinates, $this->coordinateDestination, $this->fleetMaxSpeed);
                    $this->fleet->arrival_date = Carbon::now()->add($travelTime.'s');

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();

                    $now = Carbon::now();
                    $fleetDuration = $now->diffForHumans($this->fleet->arrival_date,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);
                    $scavengeConfirmation = trans('fleet.scavengeConfirmation', [ 'mission' => ucfirst($this->fleet->mission),
                                                                'coordinateDestination' => $destCoordinates,
                                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                                'planetSource' => $this->player->activeColony->name,
                                                                'coordinateSource' => $sourceCoordinates,
                                                                'fleet' => $fleetString,
                                                                'choosedSpeed' => $this->fleetMaxSpeed,
                                                                'maxSpeed' => $this->fleetSpeed,
                                                                'fuel' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(floor($this->travelCost)),
                                                                'duration' => $fleetDuration,
                                                            ], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($scavengeConfirmation)->then(function ($messageSent){

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
                                    try
                                    {
                                        $this->player->activeColony->refresh();

                                        if($this->player->activeColony->naqahdah >= $this->travelCost)
                                            $this->player->activeColony->naqahdah -= $this->travelCost;
                                        else
                                        {
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil($this->travelCost-$this->player->activeColony->naqahdah))], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }

                                        //CHECK Units
                                        foreach($this->fleetUnits as $fleetUnit)
                                        {
                                            $unitCheck = $this->player->activeColony->hasUnitById($fleetUnit['id']);
                                            if(!($unitCheck && $unitCheck->pivot->number >= $fleetUnit['qty']))
                                            {
                                                $qtyOwned = 0;
                                                if($unitCheck)
                                                {
                                                    $qtyOwned = $unitCheck->pivot->number;
                                                    $resource = trans('craft.'.$unitCheck->slug.'.name', [], $this->player->lang);
                                                }
                                                else
                                                {
                                                    $unit = Unit::find($fleetUnit['id']);
                                                    $resource = trans('craft.'.$unit->slug.'.name', [], $this->player->lang);
                                                }

                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => $resource.': '.number_format(ceil($fleetUnit['qty']-$qtyOwned))], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                        }
                                        $this->player->activeColony->save();
                                        $this->fleet->save();

                                        //addUnitsToFleet
                                        foreach($this->fleetUnits as $fleetUnit)
                                        {
                                            $this->fleet->units()->attach([$fleetUnit['id'] => ['number' => $fleetUnit['qty']]]);
                                            $unitCheck = $this->player->activeColony->hasUnitById($fleetUnit['id']);
                                            $unitCheck->pivot->number -= $fleetUnit['qty'];
                                            if($unitCheck->pivot->number <= 0)
                                                $this->player->activeColony->units()->detach($unitCheck->id);
                                            else
                                                $unitCheck->pivot->save();
                                        }

                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('stargate.missionCancelled', [], $this->player->lang)]);
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
                }

                if(Str::startsWith('attack',$this->args[0]))
                {
                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang)." / check `!help fleet`";

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

                    $boostedString = '/';
                    if($this->boosted)
                    {
                        $boostedString = config('stargate.emotes.shield').' +15% '.config('stargate.emotes.speed').' +20% ('.config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).' '.number_format($this->boostCost,2).')';
                    }

                    $baseMsg = trans('fleet.fleetAttackMessage', [ 'mission' => ucfirst($this->fleet->mission),
                                                                'coordinateDestination' => $destCoordinates,
                                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                                'planetSource' => $this->player->activeColony->name,
                                                                'coordinateSource' => $sourceCoordinates,
                                                                'fleet' => $fleetString,
                                                                'freightCapacity' => number_format($this->usedCapacity).' / '.number_format($this->fleet->capacity),
                                                                'crew' => number_format($this->fleet->crew),
                                                                'choosedSpeed' => $this->fleetMaxSpeed,
                                                                'maxSpeed' => $this->fleetSpeed,
                                                                'boosted' => $boostedString,
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
                        $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    echo 'CONFIRMED';
                                    $this->player->activeColony->refresh();

                                    $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                                    if($raidCapability['result'] == false)
                                    {
                                        $this->paginatorMessage->channel->sendMessage($raidCapability['message']);
                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                        return;
                                    }

                                    if($this->boosted)
                                    {
                                        if($this->player->activeColony->E2PZ > $this->boostCost)
                                            $this->player->activeColony->E2PZ -= $this->boostCost;
                                        else{
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.number_format($this->boostCost - $this->player->activeColony->E2PZ,2)], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }
                                    }

                                    if($this->player->activeColony->naqahdah >= $this->travelCost)
                                        $this->player->activeColony->naqahdah -= $this->travelCost;
                                    else
                                    {
                                        $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.naqahdah').' Naqahdah: '.number_format(ceil($this->travelCost-$this->player->activeColony->naqahdah))], $this->player->lang));
                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                        return;
                                    }
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
                                        $this->player->activeColony->military -= $this->fleet->crew;
                                        $this->player->activeColony->save();

                                        //addShipToFleet
                                        foreach($this->fleetShips as $fleetShip)
                                        {
                                            $this->fleet->ships()->attach([$fleetShip['id'] => ['number' => $fleetShip['qty']]]);
                                            $shipCheck = $this->player->activeColony->hasShipById($fleetShip['id']);
                                            $shipCheck->pivot->number -= $fleetShip['qty'];
                                            if($shipCheck->pivot->number <= 0)
                                                $this->player->activeColony->ships()->detach($shipCheck->id);
                                            else
                                                $shipCheck->pivot->save();
                                            //$this->activeColony->ships()->updateExistingPivot($fleetShip['id'], array('number' => 1), false);
                                        }

                                        $attackLog = new GateFight;
                                        $attackLog->type = 'fleet';
                                        $attackLog->player_id_source = $this->player->id;
                                        $attackLog->colony_id_source = $this->player->activeColony->id;
                                        $attackLog->player_id_dest = $this->coordinateDestination->colony->player->id;
                                        $attackLog->colony_id_dest = $this->coordinateDestination->colony->id;
                                        $attackLog->fleet_id = $this->fleet->id;
                                        $attackLog->created_at = $this->fleet->arrival_date;
                                        $attackLog->save();

                                        echo '<br/>dddddd';

                                        $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
                                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
                            "title" => "Fleet",
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
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
            $baseCusumption = 2 + 150 * $galaxyDifference + 10 * $systemDifference;
        elseif($systemDifference > 1)
            $baseCusumption = 2 + 1.5 * $systemDifference;
        else
            $baseCusumption = 1 + 0.1 * $planetDifference;

        return ($baseCusumption*1.6)*$this->fleetConsumptionBonus;
    }

    public function canAttack($colonySource,$colonyDest)
    {
        $now = Carbon::now();

        $last96to120h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('120h')],['created_at', '<', Carbon::now()->sub('96h')]])->count();
        $last72to96h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('96h')],['created_at', '<', Carbon::now()->sub('72h')]])->count();
        $last48to72h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('72h')],['created_at', '<', Carbon::now()->sub('48h')]])->count();
        $last24to48h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('48h')],['created_at', '<', Carbon::now()->sub('24h')]])->count();
        $last0to24h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('24h')]])->count();

        //par 24h
        /**
         * Si en paix
         * 3 attaques par 24h dont 2 par la porte
         * 1 attaque par planète par la porte sur 24h
         *
         * pause de 72h si 2 cycles d attaques sur 72h
         *
         * Si en guerre
         * Attaques par vaisseaux illimitées
         * Attaques par la porte: 1 par colonie par 24h
         */

        if(($last48to72h >= 0 && $last72to96h > 0) || ($last48to72h >= 0 && $last96to120h > 0))
            $lastFight = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('72h')],['created_at', '<', Carbon::now()->sub('48h')]])->orderBy('created_at','DESC')->limit(1)->first();
        elseif(($last24to48h >= 0 && $last48to72h > 0) || ($last24to48h >= 0 && $last72to96h > 0))
            $lastFight = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('48h')],['created_at', '<', Carbon::now()->sub('24h')]])->orderBy('created_at','DESC')->limit(1)->first();
        elseif(($last0to24h >= 3 && $last24to48h > 0)  || ($last0to24h >= 3 && $last48to72h > 0) || ($last0to24h >= 3 && $last72to96h > 0))
            $lastFight = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('24h')]])->orderBy('created_at','DESC')->limit(1)->first();
        else
            $lastFight = null;

        if($lastFight != null)
        {
            $now = Carbon::now();
            $convertedDate = Carbon::createFromFormat("Y-m-d H:i:s",$lastFight->created_at);
            $timeUntilAttack = $now->diffForHumans($convertedDate->addHours(72),[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);
            return array('result' => false, 'message' => trans('stargate.AttackLimit', ['time' => $timeUntilAttack], $this->player->lang));
        }
        elseif($last0to24h >= 3)
        {
            $firstOf24h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('24h')]])->orderBy('created_at','ASC')->limit(1)->first();
            $now = Carbon::now();
            $convertedDate = Carbon::createFromFormat("Y-m-d H:i:s",$firstOf24h->created_at);
            $timeUntilAttack = $now->diffForHumans($convertedDate->addHours(24),[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);
            return array('result' => false, 'message' => trans('stargate.AttackLimit', ['time' => $timeUntilAttack], $this->player->lang));
        }
        return array('result' => true);
    }

    public function getPage()
    {
        $displayList = $this->fleetHistory->skip(10*($this->page -1))->take(10);

        $fleetList = '';
        $fleetList .= "ID - ARRIVAL - DESTINATION - MISSION - STATUS\n";
        foreach($displayList as $fleetElem)
        {
            switch($fleetElem->mission)
            {
                case 'attack':
                    if($this->player->id == $fleetElem->sourcePlayer->id)
                    {
                        $fleetArrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleetElem->arrival_date);
                        if(!$fleetArrivalDate->isPast())
                        {
                            $now = Carbon::now();
                            $arrivalDate = $now->diffForHumans($fleetArrivalDate,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                        }
                        else
                        {
                            $arrivalDate = substr($fleetElem->arrival_date,0,16);
                        }

                        if($fleetElem->ended)
                            $fleetStatus = trans('generic.endedf', [], $this->player->lang);
                        elseif($fleetElem->returning)
                            $fleetStatus = trans('fleet.returningStatus', [], $this->player->lang);
                        else
                            $fleetStatus = trans('fleet.ongoingStatus', [], $this->player->lang);

                        $fleetList .= trans('fleet.historyLine', [
                            'fleetId' => $fleetElem->id,
                            'arrival' => $arrivalDate,
                            'mission' => ucfirst($fleetElem->mission),
                            'destination' => $fleetElem->destinationColony->name.' ['.$fleetElem->destinationColony->coordinates->humanCoordinates().'] ('.$fleetElem->destinationPlayer->user_name.')',
                            'status' => $fleetStatus
                        ], $this->player->lang)."\n";
                    }
                    else
                    {
                        $fleetStatus = trans('fleet.ongoingStatus', [], $this->player->lang);

                        $arrivalDate = substr($fleetElem->arrival_date,0,16);
                        if(!is_null($fleetElem->gateFight) && ($fleetElem->returning || $fleetElem->ended))
                        {
                            $fleetStatus = trans('generic.endedf', [], $this->player->lang);
                            $arrivalDate = substr($fleetElem->gateFight->updated_at,0,16);
                        }
                        else
                        {
                            $now = Carbon::now();
                            $fleetArrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleetElem->arrival_date);
                            $arrivalDate = $now->diffForHumans($fleetArrivalDate,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                        }

                        $fleetList .= trans('fleet.historyLine', [
                            'fleetId' => $fleetElem->id,
                            'arrival' => $arrivalDate,
                            'mission' => ucfirst($fleetElem->mission),
                            'destination' => $fleetElem->destinationColony->name.' ['.$fleetElem->destinationColony->coordinates->humanCoordinates().'] ('.$fleetElem->destinationPlayer->user_name.')',
                            'status' => $fleetStatus
                        ], $this->player->lang)."\n";
                    }
                break;
                default:
                    $fleetStatus = trans('fleet.ongoingStatus', [], $this->player->lang);
                    if($fleetElem->returning)
                        $fleetStatus = trans('fleet.returningStatus', [], $this->player->lang);

                    $now = Carbon::now();
                    $fleetArrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$fleetElem->arrival_date);
                    $arrivalDate = $now->diffForHumans($fleetArrivalDate,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $fleetList .= trans('fleet.historyLine', [
                        'fleetId' => $fleetElem->id,
                        'arrival' => $arrivalDate,
                        'mission' => ucfirst($fleetElem->mission),
                        'destination' => $fleetElem->destinationColony->name.' ['.$fleetElem->destinationColony->coordinates->humanCoordinates().'] ('.$fleetElem->destinationPlayer->user_name.')',
                        'status' => $fleetStatus
                    ], $this->player->lang)."\n";
                break;
            }
        }

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('fleet.fleetHistory', [], $this->player->lang),
            "description" => trans('fleet.historyHowTo', [], $this->player->lang)."\n".$fleetList,
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        return $embed;
    }

    public function displayFight(Fleet $fleet)
    {
        try{
            $fightMessage = $fleet->gateFight->{'report_'.$this->player->lang};
            if(strlen($fightMessage) < 1800)
            {
                $this->message->channel->sendMessage($fightMessage);
            }
            else
            {
                $this->fightPages = [];

                if($this->player->lang == 'fr')
                {
                    $this->fightPages = explode('__Passe n',$fightMessage);
                    $lastSteps = explode('__Résultat de la',$this->fightPages[count($this->fightPages)-1]);
                    $this->fightPages[count($this->fightPages)-1] = $lastSteps[0];
                    $this->fightPages[] = $lastSteps[1];
                    foreach($this->fightPages as $key => $value){
                        if($key > 0 && $key != count($this->fightPages)-1)
                            $this->fightPages[$key] = '__Passe n'.$value;
                        elseif($key = count($this->fightPages)-1)
                            $this->fightPages[$key] = '__Résultat de la'.$value;
                    }
                }
                elseif($this->player->lang == 'en')
                {
                    $this->fightPages = explode('__Pass n',$fightMessage);
                    $lastSteps = explode('__Battle summary',$this->fightPages[count($this->fightPages)-1]);
                    $this->fightPages[count($this->fightPages)-1] = $lastSteps[0];
                    $this->fightPages[] = $lastSteps[1];
                    foreach($this->fightPages as $key => $value){
                        if($key > 0 && $key != count($this->fightPages)-1)
                            $this->fightPages[$key] = '__Pass n'.$value;
                        elseif($key = count($this->fightPages)-1)
                            $this->fightPages[$key] = '__Battle summary'.$value;
                    }
                }
                $this->closed = false;
                $this->page = 1;
                $this->maxPage = count($this->fightPages);
                $this->maxTime = time()+180;
                $this->message->channel->sendMessage($this->fightPages[0])->then(function ($messageSent){
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
                                    $this->paginatorMessage->content = trans('generic.closedList', [], $this->player->lang);
                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    /*$newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->player->lang)]);
                                    $messageReaction->message->addEmbed($newEmbed); */
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                    $this->closed = true;
                                    return;
                                }
                                elseif($messageReaction->emoji->name == '⏪')
                                    $this->page = 1;
                                elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                    $this->page--;
                                elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                    $this->page++;
                                elseif($messageReaction->emoji->name == '⏩')
                                    $this->page = $this->maxPage;

                                $this->paginatorMessage->content = $this->fightPages[($this->page -1)];
                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
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
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }
}
