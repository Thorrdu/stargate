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
use Carbon\CarbonInterface;
use App\Trade;
use App\TradeResource;
use App\SpyLog;
use App\GateFight;
use App\Pact;
use App\Reminder;
use App\Utility\PlayerUtility;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;

class Stargate extends CommandHandler implements CommandInterface
{
    public $listner;
    public $paginatorMessage;
    public $tradeResources;
    public $maxTime;
    public $coordinateDestination;
    public $attackMilitaries;
    public $attackUnits;
    public $closed;
    public $page;
    public $maxPage;


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

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $researchCenter = Building::find(7);
                $centerLevel = $this->player->activeColony->hasBuilding($researchCenter);
                if(!$centerLevel || $centerLevel < 5)
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
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
                    $newEmbed = $this->discord->factory(Embed::class,$embed);
                    $this->message->channel->sendMessage('', false, $newEmbed);
                    return;
                }

                if(count($this->args) < 2 && !(count($this->args) > 0 && Str::startsWith('bury',$this->args[0])))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
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
                    $newEmbed = $this->discord->factory(Embed::class,$embed);
                    $this->message->channel->sendMessage('', false, $newEmbed);
                    return;
                }

                if(count($this->args) > 0 && Str::startsWith('bury',$this->args[0]))
                {
                    if($this->player->activeColony->stargate_burying)
                    {
                        $burial_date = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->stargate_action_date);
                        $buryingTime = Carbon::now()->diffForHumans($burial_date,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        if($this->player->activeColony->stargate_buried)
                            return trans('stargate.digingActive', ['time' => $buryingTime], $this->player->lang);
                        else
                            return trans('stargate.buryingActive', ['time' => $buryingTime], $this->player->lang);
                    }

                    if($this->player->activeColony->stargate_buried)
                        $buryMessage = trans('stargate.digUpConfirm', [], $this->player->lang);
                    else
                        $buryMessage = trans('stargate.burryConfirm', [], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($buryMessage)->then(function ($messageSent){

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
                                    $this->player->activeColony->stargate_burying = true;
                                    if($this->player->activeColony->stargate_buried)
                                    {
                                        $this->player->activeColony->stargate_action_date = Carbon::now()->add('48h');
                                        $burialMessage = trans('stargate.digingStarted', [], $this->player->lang);
                                        $imgBury = 'http://bot.thorr.ovh/stargate/laravel/public/images/digStargate.jpg';
                                    }
                                    else
                                    {
                                        $this->player->activeColony->stargate_action_date = Carbon::now()->add('24h');
                                        $burialMessage = trans('stargate.burialStarted', [], $this->player->lang);
                                        $imgBury = 'http://bot.thorr.ovh/stargate/laravel/public/images/buryStargate.png';
                                    }
                                    $this->player->activeColony->save();

                                    $embed = [
                                        'author' => [
                                            'name' => $this->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                        ],
                                        'image' => ["url" => $imgBury],
                                        "title" => "Stargate",
                                        "description" => $burialMessage,
                                        'fields' => [
                                        ],
                                        'footer' => array(
                                            'text'  => 'Stargate',
                                        ),
                                    ];

                                    $newEmbed = $this->discord->factory(Embed::class,$embed);
                                    $messageReaction->message->addEmbed($newEmbed);
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

                if(count($this->args) >= 2 && Str::startsWith('explore',$this->args[0]) && Str::startsWith('list',$this->args[1]))
                {
                    if($this->player->explorations->count() == 0)
                        return trans('stargate.emptyExploHistory', [], $this->player->lang);

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->player->explorations->count()/10);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getPageExplo())->then(function ($messageSent){
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
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPageExplo());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                    {
                                        $this->page--;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPageExplo());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                    {
                                        $this->page++;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPageExplo());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '⏩')
                                    {
                                        $this->page = $this->maxPage;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getPageExplo());
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
                    return;
                }

                if($this->player->activeColony->stargate_buried || ($this->player->activeColony->stargate_burying && !$this->player->activeColony->stargate_buried))
                {
                    return trans('stargate.buriedStargate', [], $this->player->lang);
                }

                if(!preg_match('/(([0-9]{1,}:[0-9]{1,}:[0-9]{1,})|([0-9]{1,};[0-9]{1,};[0-9]{1,}))/', $this->args[1], $coordinatesMatch))
                {
                    if(Str::startsWith('move',$this->args[0]) && !((int)$this->args[1] > 0 && (int)$this->args[1] <= $this->player->colonies->count()))
                        return trans('colony.UnknownColony', [], $this->player->lang);
                    elseif(!Str::startsWith('move',$this->args[0]))
                        return trans('stargate.unknownCoordinates', [], $this->player->lang);

                    $this->coordinateDestination = $this->player->colonies[$this->args[1]-1]->coordinates;
                }
                else
                {
                    //Est-ce que la destination à une porte ?
                    if(strstr($coordinatesMatch[0],';'))
                        $coordinates = explode(';',$coordinatesMatch[0]);
                    else
                        $coordinates = explode(':',$coordinatesMatch[0]);

                    $this->coordinateDestination = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();
                }

                if(is_null($this->coordinateDestination))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                if(!is_null($this->coordinateDestination->colony))
                {
                    $researchCenter = Building::find(7);
                    $centerLevel = $this->coordinateDestination->colony->hasBuilding($researchCenter);
                    if(!$centerLevel || $centerLevel < 4 || $this->coordinateDestination->colony->stargate_buried)
                        return trans('stargate.failedDialing', [], $this->player->lang);

                    if(!is_null($this->coordinateDestination->colony->player->vacation))
                        return trans('profile.playerVacation', [], $this->player->lang);
                }

                if(!Str::startsWith('move',$this->args[0]) && !is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id == $this->player->id && $this->player->user_id != 125641223544373248)
                    return trans('stargate.samePlayerAction', [], $this->player->lang);

                if(Str::startsWith('move',$this->args[0]) && !is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id != $this->player->id)
                    return trans('stargate.notAColonyOfYour', [], $this->player->lang);

                if($this->coordinateDestination->id == $this->player->activeColony->coordinates->id && $this->player->user_id != 125641223544373248)
                    return trans('stargate.failedDialing', [], $this->player->lang);

                //Check Consommation E2PZ
                $travelCost = $this->getConsumption($this->player->activeColony->coordinates,$this->coordinateDestination);
                if($this->player->activeColony->E2PZ < $travelCost)
                    return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang);

                if(Str::startsWith('explore',$this->args[0]))
                {
                    if(!is_null($this->coordinateDestination->colony))
                        return trans('stargate.explorePlayerImpossible', [], $this->player->lang);

                    if($this->player->activeColony->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.ceil(1000-$this->player->activeColony->military)], $this->player->lang);

                    $currentExplo = Exploration::where([['player_id', $this->player->id],['exploration_result', null]])->count();
                    if($currentExplo > 0)
                    {
                        $communication = Technology::find(1);
                        $comLvl = $this->player->hasTechnology($communication);
                        if(!$comLvl)
                            $comLvl = 0;

                        if(ceil($comLvl/4) <= $currentExplo)
                            return trans('stargate.maxExplorationReached', [], $this->player->lang);
                    }

                    if($this->player->explorations->count() > 0)
                    {
                        $alreadyExplored = $this->player->explorations->filter(function ($value) {
                            return $value->coordinateDestination->id == $this->coordinateDestination->id;
                        });
                        if($alreadyExplored->count() > 0)
                            return trans('stargate.alreadyExplored', [], $this->player->lang);
                    }

                    //confirm
                    $exploreConfirmPower = config('stargate.emotes.military')." ".trans('generic.military',[],$this->player->lang).': '.number_format(1000);
                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $exploreConfirmation = trans('stargate.exploreConfirmation', ['militaryUnits' => $exploreConfirmPower, 'coordinateDestination' => $destCoordinates, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/manStanding.gif'],
                        "title" => "Stargate",
                        "description" => $exploreConfirmation,
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];
                    $newEmbed = $this->discord->factory(Embed::class,$embed);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent) use($travelCost){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });

                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($travelCost){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.cancelled', [], $this->player->lang)]);
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    $this->player->refresh();
                                    $this->player->activeColony->refresh();
                                    if($this->player->activeColony->military < 1000)
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,[
                                                                    'title' => trans('generic.cancelled', [], $this->player->lang),
                                                                    'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.ceil(1000-$this->player->activeColony->military)], $this->player->lang)
                                                                    ]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                        return;
                                    }

                                    //Check Consommation E2PZ
                                    if($this->player->activeColony->E2PZ < $travelCost)
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,[
                                            'title' => trans('generic.cancelled', [], $this->player->lang),
                                            'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang)
                                            ]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                        return;
                                    }

                                    $currentExplo = Exploration::where([['player_id', $this->player->id],['exploration_result', null]])->count();
                                    if($currentExplo > 0)
                                    {
                                        $communication = Technology::find(1);
                                        $comLvl = $this->player->hasTechnology($communication);
                                        if(!$comLvl)
                                            $comLvl = 0;

                                        if(ceil($comLvl/4) <= $currentExplo)
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                                    'title' => trans('generic.cancelled', [], $this->player->lang),
                                                                    'description' => trans('stargate.maxExplorationReached', [], $this->player->lang)
                                                                    ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }
                                    }

                                    if($this->player->explorations->count() > 0)
                                    {
                                        $alreadyExplored = $this->player->explorations->filter(function ($value) {
                                            return $value->coordinateDestination->id == $this->coordinateDestination->id;
                                        });
                                        if($alreadyExplored->count() > 0)
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                                    'title' => trans('generic.cancelled', [], $this->player->lang),
                                                                    'description' => trans('stargate.alreadyExplored', [], $this->player->lang)
                                                                    ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }
                                    }

                                    $this->player->activeColony->military -= 1000;
                                    $this->player->activeColony->E2PZ -= $travelCost;
                                    $this->player->activeColony->save();

                                    $exploration = new Exploration;
                                    $exploration->player_id = $this->player->id;
                                    $exploration->colony_source_id = $this->player->activeColony->id;
                                    $exploration->coordinate_destination_id = $this->coordinateDestination->id;
                                    $exploration->exploration_end = Carbon::now()->addMinutes(rand(60,240));
                                    $exploration->save();

                                    $embed = [
                                        'author' => [
                                            'name' => $this->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                        ],
                                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/exploration.gif'],
                                        "title" => "Stargate",
                                        "description" => trans('stargate.explorationSent', ['coordinates' => $this->coordinateDestination->galaxy.':'.$this->coordinateDestination->system.':'.$this->coordinateDestination->planet], $this->player->lang),
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
                            catch(\Exception $e)
                            {
                                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                            }
                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                        });
                    });
                    return;
                }

                if(Str::startsWith('move',$this->args[0]))
                {
                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang).' / !s move [Coordinates] Ress1 Qty1';

                    $availableResources = config('stargate.resources');
                    $availableResources[] = 'E2PZ';
                    $availableResources[] = 'military';

                    $this->tradeResources = [];
                    $capacityNeeded = 0;
                    $tradeString = "";
                    //$tradeCapacity = 0;

                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if(is_numeric($this->args[$cptRes+1]) && $this->args[$cptRes+1] > 0)
                                $qty = $this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            $resource = $this->args[$cptRes];
                            if(Str::startsWith('e2pz',$resource) || Str::startsWith('zpm',$resource) || Str::startsWith('ZPM',$resource))
                                $resource = 'E2PZ';

                            if($resource == 'E2PZ')
                                $qty = round($qty,2);
                            else
                                $qty = (int)$qty;

                            $resFound = $bypass = false;
                            foreach($availableResources as $availableResource)
                            {
                                if(Str::startsWith($availableResource,$resource) && !isset($this->tradeResources[$availableResource]))
                                {
                                    $resource = $availableResource;

                                    $resFound = true;
                                    if(!in_array($resource,array('military','E2PZ')))
                                        $capacityNeeded += $qty;

                                    $resQty = number_format($qty);
                                    if($resource == 'E2PZ')
                                        $resQty = number_format($qty,2);

                                    $tradeString .= config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.$resQty."\n";

                                    if($resource != 'E2PZ' && $resource != 'military' && $this->coordinateDestination->colony->{'storage_'.$resource} < ($this->coordinateDestination->colony->$resource + $qty))
                                        return trans('stargate.tradeStorageTooLow', ['resource' => config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource)], $this->player->lang);

                                    $this->tradeResources[$resource] = $qty;
                                }
                                elseif(isset($this->tradeResources[$availableResource]))
                                    $bypass = true;
                            }
                            if(!$resFound && !$bypass)
                            {
                                $unit = Unit::Where('slug', 'LIKE', $resource.'%')->first();
                                if(is_null($unit))
                                    return trans('stargate.unknownResource', ['resource' => $resource], $this->player->lang);
                                elseif(!isset($this->tradeResources[$unit->slug]))
                                {
                                    $resFound = true;
                                    $resource = $unit->slug;
                                    $tradeString .= trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)."\n";
                                    $this->tradeResources[$resource] = $qty;
                                    //$tradeCapacity += $unit->capacity;
                                }
                            }
                        }
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);
                    }

                    $tradeCapacity = $this->player->activeColony->tradeCapacity();
                    if($tradeCapacity < $capacityNeeded)
                        return trans('generic.notEnoughCapacity', ['missingCapacity' => number_format(round($capacityNeeded - $tradeCapacity))], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();

                    $tradeMsg = trans('stargate.moveMessage', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'planetSource' => $this->player->activeColony->name, 'coordinateSource' => $sourceCoordinates, 'planet' => $this->coordinateDestination->colony->name, 'freightCapacity' => number_format($capacityNeeded).'/'.number_format($tradeCapacity), 'resources' => $tradeString, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($tradeMsg)->then(function ($messageSent) use($travelCost){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });


                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($travelCost){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    echo 'CONFIRMED';
                                    $this->player->activeColony->refresh();
                                    $this->coordinateDestination->colony->refresh();

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
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                            elseif($ownedUnits < $qty)
                                            {
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty-$ownedUnits)], $this->player->lang));

                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                            $receivedString .= trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)."\n";
                                        }
                                        elseif($this->player->activeColony->$tradeResource < $qty || ($tradeResource == 'E2PZ' && $this->player->activeColony->$tradeResource < ($qty+$travelCost)))
                                        {
                                            if($tradeResource == 'E2PZ')
                                                $qty += $travelCost;

                                            $resQty = number_format(ceil($qty-$this->player->activeColony->$tradeResource));
                                            if($tradeResource == 'E2PZ')
                                                $resQty = number_format(($qty-$this->player->activeColony->$tradeResource),2);

                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($tradeResource))." ".ucfirst($tradeResource).': '.$resQty], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }
                                        else
                                        {
                                            $tradeObjets[] = ['resource' => $tradeResource,'quantity'=>$qty];
                                            $resQty = number_format($qty);
                                            if($tradeResource == 'E2PZ')
                                                $resQty = number_format($qty,2);
                                            $receivedString .= config('stargate.emotes.'.strtolower($tradeResource))." ".ucfirst($tradeResource).': '.$resQty."\n";
                                        }
                                    }

                                    try{
                                        //Check Consommation E2PZ
                                        if($this->player->activeColony->E2PZ < $travelCost)
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                'title' => trans('generic.cancelled', [], $this->player->lang),
                                                'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang)
                                                ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }

                                        foreach($tradeObjets as $tradeObject)
                                        {
                                            $tradeObject['quantity'] = $tradeObject['quantity'];
                                            if(isset($tradeObject['unit_id']))
                                            {
                                                $tradeUnit = Unit::find($tradeObject['unit_id']);

                                                $unitExists = $this->coordinateDestination->colony->units->filter(function ($value) use($tradeUnit){
                                                    return $value->id == $tradeUnit->id;
                                                });
                                                if($unitExists->count() > 0)
                                                {
                                                    $unitToUpdate = $unitExists->first();
                                                    $unitToUpdate->pivot->number += $tradeObject['quantity'];
                                                    $unitToUpdate->pivot->save();
                                                }
                                                else
                                                {
                                                    $this->coordinateDestination->colony->units()->attach([$tradeObject['unit_id'] => ['number' => $tradeObject['quantity']]]);
                                                }
                                                $unitExists = $this->player->activeColony->units->filter(function ($value) use($tradeUnit){
                                                    return $value->id == $tradeUnit->id;
                                                });
                                                $unitTodown = $unitExists->first();
                                                $unitTodown->pivot->number -= $tradeObject['quantity'];
                                                $unitTodown->pivot->save();
                                            }
                                            elseif(isset($tradeObject['resource']))
                                            {
                                                $this->player->activeColony->{$tradeObject['resource']} -= $tradeObject['quantity'];
                                                $this->coordinateDestination->colony->{$tradeObject['resource']} += $tradeObject['quantity'];
                                            }
                                        }

                                        $this->player->activeColony->E2PZ -= $travelCost;
                                        $this->player->activeColony->save();
                                        $this->coordinateDestination->colony->save();
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }

                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
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

                if(Str::startsWith('trade',$this->args[0]))
                {
                    if(is_null($this->coordinateDestination->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    if($this->coordinateDestination->colony->player->npc)
                        return trans('stargate.tradeNpcImpossible', [], $this->player->lang);

                    $pactExists = Pact::Where([['player_1_id', $this->player->id], ['player_2_id', $this->coordinateDestination->colony->player->id]])->orWhere([['player_2_id', $this->player->id], ['player_1_id', $this->coordinateDestination->colony->player->id]])->get()->first();
                    if(is_null($pactExists) && $this->player->user_id != 125641223544373248)
                        return trans('trade.noPactWithThisPlayer', [] , $this->player->lang);

                    if($this->player->trade_ban)
                        return trans('stargate.trade_ban', [], $this->player->lang);
                    elseif($this->coordinateDestination->colony->player->ban)
                        return trans('stargate.playerTradeBan', [], $this->player->lang);
                    elseif($this->player->user_id != config('stargate.ownerId'))
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

                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang).' / !s trade [Coordinates] Ress1 Qty1';

                    $availableResources = config('stargate.resources');
                    $availableResources[] = 'E2PZ';
                    $availableResources[] = 'military';

                    $this->tradeResources = [];
                    $capacityNeeded = 0;
                    $tradeString = "";
                    //$tradeCapacity = 0;

                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if(is_numeric($this->args[$cptRes+1]) && $this->args[$cptRes+1] > 0)
                                $qty = $this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            $resource = $this->args[$cptRes];
                            if(Str::startsWith('e2pz',$resource) || Str::startsWith('zpm',$resource) || Str::startsWith('ZPM',$resource))
                                $resource = 'E2PZ';

                            if($resource == 'E2PZ')
                                $qty = round($qty,2);
                            else
                                $qty = (int)$qty;

                            $resFound = $bypass = false;
                            foreach($availableResources as $availableResource)
                            {
                                if(Str::startsWith($availableResource,$resource) && !isset($this->tradeResources[$availableResource]))
                                {
                                    $resource = $availableResource;

                                    $resFound = true;
                                    if(!in_array($resource,array('military','E2PZ')))
                                        $capacityNeeded += $qty;

                                    $resQty = number_format($availableResource);
                                    if($availableResource == 'E2PZ')
                                        $resQty = number_format($availableResource,2);

                                    $tradeString .= config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.$resQty."\n";

                                    if($resource != 'E2PZ' && $resource != 'military' && $this->coordinateDestination->colony->{'storage_'.$resource} < ($this->coordinateDestination->colony->$resource + $qty))
                                        return trans('stargate.tradeStorageTooLow', ['resource' => config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource)], $this->player->lang);

                                    $this->tradeResources[$resource] = $qty;
                                }
                                elseif(isset($this->tradeResources[$availableResource]))
                                    $bypass = true;
                            }
                            if(!$resFound && !$bypass)
                            {
                                $unit = Unit::Where('slug', 'LIKE', $resource.'%')->first();
                                if(is_null($unit))
                                    return trans('stargate.unknownResource', ['resource' => $resource], $this->player->lang);
                                elseif(!isset($this->tradeResources[$unit->slug]))
                                {
                                    $resFound = true;
                                    $resource = $unit->slug;
                                    $tradeString .= trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)."\n";
                                    $this->tradeResources[$resource] = $qty;
                                    //$tradeCapacity += $unit->capacity;
                                }
                            }
                        }
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);
                    }

                    $tradeCapacity = $this->player->activeColony->tradeCapacity();
                    if($tradeCapacity < $capacityNeeded)
                        return trans('generic.notEnoughCapacity', ['missingCapacity' => number_format(round($capacityNeeded - $tradeCapacity))], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();

                    $tradeMsg = trans('stargate.tradeMessage', ['coordinateDestination' => $destCoordinates, 'coordinateSource' => $sourceCoordinates, 'planetSource' => $this->player->activeColony->name, 'planetDest' => $this->coordinateDestination->colony->name, 'freightCapacity' => number_format($capacityNeeded).'/'.number_format($tradeCapacity), 'resources' => $tradeString, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($tradeMsg)->then(function ($messageSent) use($travelCost){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });


                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($travelCost){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    $this->player->activeColony->refresh();
                                    $this->coordinateDestination->colony->refresh();

                                    //Check Consommation E2PZ
                                    if($this->player->activeColony->E2PZ < $travelCost)
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,[
                                            'title' => trans('generic.cancelled', [], $this->player->lang),
                                            'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang)
                                            ]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                        return;
                                    }

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
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                            elseif($ownedUnits < $qty)
                                            {
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty-$ownedUnits)], $this->player->lang));
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                return;
                                            }
                                            $receivedString .= trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($qty)."\n";
                                        }
                                        elseif($this->player->activeColony->$tradeResource < $qty || ($tradeResource == 'E2PZ' && $this->player->activeColony->$tradeResource < ($qty+$travelCost)))
                                        {
                                            $resQty = number_format(ceil($qty-$this->player->activeColony->$tradeResource));
                                            if($tradeResource == 'E2PZ')
                                            {
                                                $qty += $travelCost;
                                                $resQty = number_format(($qty-$this->player->activeColony->$tradeResource),2);
                                            }

                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.'.strtolower($tradeResource))." ".ucfirst($tradeResource).': '.$resQty], $this->player->lang));
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            return;
                                        }
                                        else
                                        {
                                            $tradeObjets[] = ['resource' => $tradeResource,'quantity'=>$qty];
                                            $resQty = number_format($qty);
                                            if($tradeResource == 'E2PZ')
                                                $resQty = number_format($qty,2);

                                            $receivedString .= config('stargate.emotes.'.strtolower($tradeResource))." ".ucfirst($tradeResource).': '.$resQty."\n";
                                        }
                                    }

                                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                    $embed = [
                                        'author' => [
                                            'name' => $this->coordinateDestination->colony->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                        ],
                                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/bouteille.gif'],
                                        "title" => "Stargate",
                                        "description" => trans('stargate.tradeReceived', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'planetSource' => $this->player->activeColony->name, 'coordinateSource' => $sourceCoordinates, 'player' => $this->player->user_name, 'resources' => $receivedString], $this->coordinateDestination->colony->player->lang),
                                        'fields' => [
                                        ],
                                        'footer' => array(
                                            'text'  => 'Stargate',
                                        ),
                                    ];

                                    $reminder = new Reminder;
                                    $reminder->reminder_date = Carbon::now()->add('1s');
                                    $reminder->embed = json_encode($embed);
                                    $reminder->player_id = $this->coordinateDestination->colony->player->id;
                                    $reminder->save();

                                    $reminder = new Reminder;
                                    $reminder->reminder_date = Carbon::now()->add('1s');
                                    $reminder->reminder = trans('stargate.tradeSent',['coordinateDestination' => $destCoordinates, 'coordinateSource' => $sourceCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'planetSource' => $this->player->activeColony->name, 'player' => $this->coordinateDestination->colony->player->user_name, 'resources' => $receivedString, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);
                                    $reminder->player_id = $this->player->id;
                                    $reminder->save();

                                    try{

                                        $tradeLogCheck = Trade::where([['player_id_dest',$this->coordinateDestination->colony->player->id], ['player_id_source',$this->player->id], ['active', true]])
                                                                ->orWhere([['player_id_source',$this->coordinateDestination->colony->player->id], ['player_id_dest',$this->player->id], ['active', true]])->first();

                                        if(!is_null($tradeLogCheck))
                                        {
                                            $tradeLog = $tradeLogCheck;
                                            $tradePlayer = '';
                                            if($this->player->id == $tradeLog->player_id_source)
                                                $tradePlayer = 1;
                                            else
                                                $tradePlayer = 2;
                                        }
                                        else
                                        {
                                            $tradeLog = new Trade;
                                            $tradeLog->player_id_source = $this->player->id;
                                            $tradeLog->player_id_dest = $this->coordinateDestination->colony->player->id;
                                            $tradeLog->trade_value_player1 = 0;
                                            $tradeLog->trade_value_player2 = 0;
                                            $tradeLog->save();
                                            $tradePlayer = 1;
                                        }

                                        foreach($tradeObjets as $tradeObject)
                                        {
                                            if(isset($tradeObject['unit_id']))
                                            {
                                                $tradeResourceExist = $tradeLog->tradeResources->filter(function ($value) use($tradeObject,$tradePlayer){
                                                    return $value->unit_id == $tradeObject['unit_id'] && $value->player == $tradePlayer;
                                                });
                                                if($tradeResourceExist->count() > 0)
                                                {
                                                    $tradeResource = $tradeResourceExist->first();
                                                    $tradeResource->quantity += $tradeObject['quantity'];
                                                }
                                                else
                                                {
                                                    $tradeResource = new TradeResource;
                                                    $tradeResource->player = $tradePlayer;
                                                    $tradeResource->trade_id = $tradeLog->id;
                                                    $tradeResource->quantity = $tradeObject['quantity'];
                                                    $tradeResource->unit_id = $tradeObject['unit_id'];
                                                    $tradeResource->load('unit');
                                                }

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

                                                $tradeResourceExist = $tradeLog->tradeResources->filter(function ($value) use($tradeObject,$tradePlayer){
                                                    return $value->resource == $tradeObject['resource'] && $value->player == $tradePlayer;
                                                });
                                                if($tradeResourceExist->count() > 0)
                                                {
                                                    $tradeResource = $tradeResourceExist->first();
                                                    $tradeResource->quantity += $tradeObject['quantity'];
                                                }
                                                else
                                                {
                                                    $tradeResource = new TradeResource;
                                                    $tradeResource->player = $tradePlayer;
                                                    $tradeResource->trade_id = $tradeLog->id;
                                                    if(isset($tradeObject['resource']))
                                                        $tradeResource->resource = $tradeObject['resource'];
                                                    $tradeResource->quantity = $tradeObject['quantity'];
                                                    if(isset($tradeObject['unit_id']))
                                                        $tradeResource->unit_id = $tradeObject['unit_id'];
                                                    $tradeResource->load('unit');
                                                }

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
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
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

                    $malp = Unit::where('slug', 'malp')->first();
                    $malpNumber = $this->player->activeColony->hasCraft($malp);
                    if(!$malpNumber)
                        return trans('generic.notEnoughResources', ['missingResources' => $malp->name.': 1'], $this->player->lang);
                    elseif($malpNumber == 0)
                        return trans('generic.notEnoughResources', ['missingResources' => $malp->name.': 1'], $this->player->lang);

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $spyMessage = trans('stargate.spyConfirmation', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3).' '.$malp->name.': 1'], $this->player->lang);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage($spyMessage)->then(function ($messageSent) use($travelCost,$sourceCoordinates,$destCoordinates,$malp){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });

                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector)  use($travelCost,$sourceCoordinates,$destCoordinates,$malp){
                            $messageReaction = $collector->first();
                            try{

                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    try
                                    {
                                        $this->player->activeColony->refresh();

                                        //Check Consommation E2PZ
                                        if($this->player->activeColony->E2PZ < $travelCost)
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                'title' => trans('generic.cancelled', [], $this->player->lang),
                                                'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang)
                                                ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }

                                        $current = Carbon::now();
                                        $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->coordinateDestination->colony->last_claim);
                                        if($current->diffInMinutes($lastClaim) > 720){
                                            $this->coordinateDestination->colony->checkColony();
                                            $this->coordinateDestination->load('colony');
                                        }

                                        $this->player->activeColony->E2PZ -= $travelCost;
                                        $this->player->activeColony->save();

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
                                        $spyConfirmedMessage = trans('stargate.spySending', ['coordinateDestination' => $destCoordinates, 'planetDest' => $this->coordinateDestination->colony->name, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3).' '.$malp->name.': 1'], $this->player->lang);

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
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
                                        $newEmbed = $this->discord->factory(Embed::class,$embed);
                                        $messageReaction->message->addEmbed($newEmbed);

                                        PlayerUtility::spy($this->player->activeColony, $this->coordinateDestination->colony);

                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
                                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                            }
                        });
                    });
                }

                if(Str::startsWith('colonize',$this->args[0]))
                {
                    if(!is_null($this->coordinateDestination->colony))
                        return trans('stargate.playerOwned', [], $this->player->lang);

                    if($this->player->activeColony->military < 1000)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.ceil(1000-$this->player->activeColony->military)], $this->player->lang);

                    $possibleColonies = $maxColonies = config('stargate.maxColonies');
                    $maxColonies += 1;
                    $colonyMaxBonusList = $this->player->artifacts->filter(function ($value){
                        return $value->bonus_category == 'ColonyMax';
                    });
                    foreach($colonyMaxBonusList as $colonyMaxBonus)
                    {
                        $possibleColonies += $colonyMaxBonus->bonus_coef;
                    }
                    if($possibleColonies > $maxColonies)
                        $possibleColonies = $maxColonies;

                    if($this->player->colonies->count() < $possibleColonies)
                    {
                        //confirm
                        $colonizeConfirmPower = config('stargate.emotes.military')." ".trans('generic.military',[],$this->player->lang).': '.number_format(1000);
                        $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                        $destCoordinates = $this->coordinateDestination->humanCoordinates();
                        $colonizeConfirmation = trans('stargate.colonizeConfirmation', ['militaryUnits' => $colonizeConfirmPower, 'coordinateDestination' => $destCoordinates, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);
                        $embed = [
                            'author' => [
                                'name' => $this->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/manStanding.gif'],
                            "title" => "Stargate",
                            "description" => $colonizeConfirmation,
                            'fields' => [
                            ],
                            'footer' => array(
                                'text'  => 'Stargate',
                            ),
                        ];
                        $newEmbed = $this->discord->factory(Embed::class,$embed);

                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent) use($travelCost,$possibleColonies){

                            $this->paginatorMessage = $messageSent;
                            $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                });
                            });

                            $filter = function($messageReaction){
                                return $messageReaction->user_id == $this->player->user_id;
                            };
                            $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($travelCost,$possibleColonies){
                                $messageReaction = $collector->first();
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.cancelled', [], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                    {
                                        $this->player->refresh();

                                        //Check Consommation E2PZ
                                        if($this->player->activeColony->E2PZ < $travelCost)
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                'title' => trans('generic.cancelled', [], $this->player->lang),
                                                'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang)
                                                ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }

                                        if($this->player->colonies->count() < $possibleColonies)
                                        {
                                            $this->player->activeColony->refresh();

                                            $this->player->activeColony->military -= 1000;
                                            $this->player->activeColony->E2PZ -= $travelCost;
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
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                        }
                                        else
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                                        'title' => trans('generic.cancelled', [], $this->player->lang),
                                                                        'description' => trans('stargate.toManyColonies', [], $this->player->lang)
                                                                        ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }
                                    }
                                }
                                catch(\Exception $e)
                                {
                                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                }
                                $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                            });
                        });
                        return;
                    }
                    else
                    {
                        return trans('stargate.toManyColonies', [], $this->player->lang);
                    }

                }

                if(Str::startsWith('attack',$this->args[0]))
                {
                    if(count($this->args) < 4)
                        return trans('generic.missingArgs', [], $this->player->lang).' / !stargate attack [Coordinates] [military/transporter] [quantity]';

                    if(is_null($this->coordinateDestination->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    if(!$this->player->isRaidable($this->coordinateDestination->colony->player) && $this->coordinateDestination->colony->player->npc == 0)
                        return trans('stargate.weakOrStrong', [], $this->player->lang);

                    /*
                    $lastHourly = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->last_hourly);
                    if($lastHourly->diffInHours($now) >= 2)
                    */
                    $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                    if($raidCapability['result'] == false)
                        return $raidCapability['message'];

                    $capacityNeeded = 0;
                    $attackConfirmPower = "";
                    $this->attackMilitaries = 0;
                    $this->attackUnits = [];

                    for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                    {
                        if(isset($this->args[$cptRes+1]))
                        {
                            if(is_numeric($this->args[$cptRes+1]) && $this->args[$cptRes+1] > 0)
                                $qty = (int)$this->args[$cptRes+1];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            $resource = $this->args[$cptRes];
                            $resFound = $bypass = false;
                            if(Str::startsWith('military',$resource))
                            {
                                if(empty($this->attackMilitaries))
                                {
                                    $resource = 'military';
                                    if($this->player->activeColony->military < $qty)
                                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.number_format($qty-$this->player->activeColony->military)], $this->player->lang);

                                    $this->attackMilitaries = $qty;
                                    $attackConfirmPower .= config('stargate.emotes.'.strtolower($resource))." ".ucfirst($resource).': '.number_format($qty)."\n";
                                }
                            }
                            else
                            {
                                $unit = Unit::Where('slug', 'LIKE', $resource.'%')->first();
                                if(is_null($unit) || $unit->type != 'Transport')
                                    return trans('stargate.unknownResource', ['resource' => $resource], $this->player->lang);
                                else
                                {
                                    $resFound = true;
                                    $resource = $unit->slug;
                                    $unitName = trans('craft.'.$unit->slug.'.name', [], $this->player->lang);
                                    if(!strstr($attackConfirmPower,$unitName))
                                    $attackConfirmPower .= $unitName.': '.number_format($qty)."\n";
                                    $this->attackUnits[] = ['qty' => $qty, 'unit' => $unit];

                                    $unitOwned = $this->player->activeColony->hasCraft($unit);
                                    if(!$unitOwned)
                                        return trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.$qty], $this->player->lang);
                                    elseif($unitOwned < $qty)
                                        return trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.($qty-$unitOwned)], $this->player->lang);

                                }
                            }
                        }
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);
                    }
                    if($this->attackMilitaries < 100)
                    {
                        if($this->attackMilitaries < 100)
                        return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.round(100-$this->attackMilitaries,2)], $this->player->lang);
                    }

                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                    $attackConfirmation = trans('stargate.AttackConfirmation', ['militaryUnits' => $attackConfirmPower,'planetName' => $this->coordinateDestination->colony->name, 'coordinateDestination' => $destCoordinates,'planetNameSource' => $this->player->activeColony->name, 'coordinateSource' => $sourceCoordinates, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);

                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/manStanding.gif'],
                        "title" => "Stargate",
                        "description" => $attackConfirmation,
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];
                    $newEmbed = $this->discord->factory(Embed::class,$embed);

                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent) use($travelCost){

                        $this->paginatorMessage = $messageSent;
                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                            });
                        });

                        $filter = function($messageReaction){
                            return $messageReaction->user_id == $this->player->user_id;
                        };
                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($travelCost){
                            $messageReaction = $collector->first();
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('stargate.attackCancelled', [], $this->player->lang)]);
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                {
                                    try{

                                        $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                                        if($raidCapability['result'] == false)
                                            $messageReaction->message->channel->sendMessage($raidCapability['message']);

                                        //Check Consommation E2PZ
                                        if($this->player->activeColony->E2PZ < $travelCost)
                                        {
                                            $newEmbed = $this->discord->factory(Embed::class,[
                                                'title' => trans('generic.cancelled', [], $this->player->lang),
                                                'description' => trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang)
                                                ]);
                                            $messageReaction->message->addEmbed($newEmbed);
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                            return;
                                        }

                                        $this->player->activeColony->refresh();
                                        foreach($this->attackUnits as $attackUnit)
                                        {
                                            $unit = $attackUnit['unit'];
                                            $ownedUnits = $this->player->activeColony->hasCraft($unit);
                                            if(!$ownedUnits)
                                            {
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($attackUnit['qty'])], $this->player->lang));
                                                return;
                                            }
                                            elseif($ownedUnits < $attackUnit['qty'])
                                            {
                                                $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => trans('craft.'.$unit->slug.'.name', [], $this->player->lang).': '.number_format($attackUnit['unit']-$ownedUnits)], $this->player->lang));
                                                return;
                                            }
                                        }
                                        if($this->player->activeColony->military < $this->attackMilitaries)
                                        {
                                            $this->paginatorMessage->channel->sendMessage(trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.number_format($this->attackMilitaries-$this->player->activeColony->military)], $this->player->lang));
                                            return;
                                        }

                                        $current = Carbon::now();
                                        $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->coordinateDestination->colony->last_claim);
                                        if($current->diffInMinutes($lastClaim) > 720){
                                            $this->coordinateDestination->colony->checkColony();
                                            $this->coordinateDestination->load('colony');
                                        }
                                        $this->player->activeColony->E2PZ -= $travelCost;
                                        $this->player->activeColony->save();

                                        $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                                        $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                        $attackSentMessage = trans('stargate.attackSent', ['planet' => $this->coordinateDestination->colony->name,'coordinateDestination' => $destCoordinates], $this->player->lang);

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                            ],
                                            'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/moveMoveMove.gif'],
                                            "title" => "Stargate",
                                            "description" => $attackSentMessage,
                                            'fields' => [
                                            ],
                                            'footer' => array(
                                                'text'  => 'Stargate',
                                            ),
                                        ];
                                        $newEmbed = $this->discord->factory(Embed::class,$embed);
                                        $messageReaction->message->addEmbed($newEmbed);

                                        $defencesAttackPoint = 0;
                                        foreach($this->coordinateDestination->colony->defences as $defence)
                                        {
                                            $defencesAttackPoint += $defence->fire_power * $defence->pivot->number;
                                        }
                                        $armamentTec = Technology::Where('slug', 'LIKE', 'armament')->first();
                                        $armamentLvl = $this->coordinateDestination->colony->player->hasTechnology($armamentTec);
                                        if($armamentLvl)
                                            $defencesAttackPoint *= pow(1.1,$armamentLvl);

                                        $defenceMilitary = floor($this->coordinateDestination->colony->military);
                                        if($defenceMilitary < 1)
                                            $defenceMilitary = 1;
                                        $defenceValue = $defenceMilitary + $defencesAttackPoint*20;

                                        /*Pertes
                                        (Nb Mili Défenseur)² / (Nb Mili Attaquant) */
                                        $attackerLoosing = floor(pow($defenceValue,2) / $this->attackMilitaries);
                                        $attackerLooseString = "";
                                        $attackerWinString = "";
                                        $defenderLooseString = "";
                                        $defenderWinString = "";
                                        $estimatedAttackTroops = config('stargate.emotes.military')." ".trans('generic.military', [], $this->coordinateDestination->colony->player->lang).': '.number_format($this->attackMilitaries*(mt_rand(50,150)/100))."\n";

                                        foreach(config('stargate.resources') as $resource)
                                        {
                                            ${$resource} = 0;
                                        }

                                        if( $attackerLoosing >= $this->attackMilitaries )
                                        {
                                            //LOST
                                            $winState = false;
                                            $defenderLostMilitaries = floor(pow($this->attackMilitaries,2) / $defenceValue);
                                            $stolenMilitaries = ceil($this->attackMilitaries/5);

                                            if($defenderLostMilitaries <= $this->coordinateDestination->colony->military)
                                                $this->coordinateDestination->colony->military -= $defenderLostMilitaries;
                                            else
                                            {
                                                $defenderLostMilitaries = floor($this->coordinateDestination->colony->military);
                                                $this->coordinateDestination->colony->military = 0;
                                            }
                                            $this->coordinateDestination->colony->military += $stolenMilitaries;

                                            $attackerLooseString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.number_format($this->attackMilitaries)."\n";
                                            $defenderWinString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->coordinateDestination->colony->player->lang).': '.number_format($stolenMilitaries)."\n";
                                            $defenderLooseString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->coordinateDestination->colony->player->lang).': '.number_format($defenderLostMilitaries)."\n";

                                            $this->player->activeColony->military -= $this->attackMilitaries;

                                            foreach($this->attackUnits as $attackUnit)
                                            {
                                                $uniQtyStolen = ceil($attackUnit['qty']/5);
                                                $defenderWinString .= $attackUnit['unit']->name.': '.number_format($uniQtyStolen)."\n";
                                                $attackerLooseString .= $attackUnit['unit']->name.': '.number_format($attackUnit['qty'])."\n";

                                                $unitAttackerExists = $this->player->activeColony->units->filter(function ($value) use($attackUnit){
                                                    return $value->id == $attackUnit['unit']->id;
                                                });
                                                if($unitAttackerExists->count() > 0)
                                                {
                                                    $unitToUpdate = $unitAttackerExists->first();
                                                    $unitToUpdate->pivot->number -= $attackUnit['qty'];
                                                    $unitToUpdate->pivot->save();
                                                }

                                                $unitExists = $this->coordinateDestination->colony->units->filter(function ($value) use($attackUnit){
                                                    return $value->id == $attackUnit['unit']->id;
                                                });
                                                if($unitExists->count() > 0)
                                                {
                                                    $unitToUpdate = $unitExists->first();
                                                    $unitToUpdate->pivot->number += $uniQtyStolen;
                                                }
                                                else
                                                {
                                                    $this->coordinateDestination->colony->units()->attach([$attackUnit['unit']->id => ['number' => $uniQtyStolen]]);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            //WIN
                                            $winState = true;

                                            if($attackerLoosing == 0)
                                            {
                                                //Aucune pertes
                                                $attackerLooseString .= trans('stargate.noCasuality', [], $this->player->lang)."\n";
                                            }
                                            else
                                            {
                                                $this->player->activeColony->military -= floor($attackerLoosing);
                                                $attackerLooseString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).": ".number_format($attackerLoosing)."\n";
                                            }

                                            //GAINS Militaires
                                            //(Nb Mili Défenseur)/5
                                            if($defenceMilitary > 0)
                                            {
                                                $stolenMilitaries = floor($defenceMilitary/5);
                                                $defenderLostMilitaries = floor($defenceMilitary*0.9);
                                                $attackerWinString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->player->lang).': '.number_format($stolenMilitaries)."\n";
                                                $defenderLooseString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->coordinateDestination->colony->player->lang).': '.number_format($defenderLostMilitaries)."\n";

                                                $this->coordinateDestination->colony->military -= $defenderLostMilitaries;
                                                $this->player->activeColony->military += $stolenMilitaries;
                                            }

                                            $totalCapacity = 0;
                                            foreach($this->attackUnits as $attackUnit)
                                            {
                                                if(!is_null($attackUnit['unit']->capacity))
                                                    $totalCapacity += $attackUnit['unit']->capacity * $attackUnit['qty'];
                                            }

                                            if($totalCapacity > 0)
                                            {
                                                $totalResource = 0;
                                                foreach(config('stargate.resources') as $resource)
                                                {
                                                    $totalResource += $this->coordinateDestination->colony->$resource;
                                                }
                                                $claimAll = false;
                                                if($totalCapacity >= ($totalResource*0.5))
                                                    $claimAll = true;

                                                foreach(config('stargate.resources') as $resource)
                                                {
                                                    if($this->coordinateDestination->colony->$resource > 1)
                                                    {
                                                        $ratio = $this->coordinateDestination->colony->$resource / $totalResource;
                                                        $maxClaimable = ceil($this->coordinateDestination->colony->$resource * 0.5);

                                                        $claimed = 0;
                                                        if($claimAll)
                                                            $claimed = $maxClaimable;
                                                        else
                                                            $claimed = floor($totalCapacity*$ratio);

                                                        if($claimed > 0)
                                                        {
                                                            $attackerWinString .= config('stargate.emotes.'.strtolower($resource)).' '.ucfirst($resource).": ".number_format($claimed)."\n";
                                                            $this->player->activeColony->$resource += $claimed;
                                                            $this->coordinateDestination->colony->$resource -= $claimed;
                                                            $defenderLooseString .= config('stargate.emotes.'.strtolower($resource)).' '.ucfirst($resource).": ".number_format($claimed)."\n";
                                                            ${$resource} = $claimed;
                                                        }
                                                    }
                                                }

                                            }
                                            /*
                                            //70% de la def restante
                                             */
                                            foreach($this->coordinateDestination->colony->defences as $defence)
                                            {
                                                $lostDefenceQty = ceil($defence->pivot->number*0.3);
                                                $newDefenceQty = floor($defence->pivot->number*0.7);
                                                $defenderLooseString .= trans('defence.'.$defence->slug.'.name', [], $this->coordinateDestination->colony->player->lang).': '.number_format($lostDefenceQty)."\n";

                                                if($newDefenceQty > 0)
                                                {
                                                    $defence->pivot->number = $newDefenceQty;
                                                    $defence->pivot->save();
                                                }
                                                else
                                                    $this->coordinateDestination->colony->defences()->detach($defence->id);
                                            }
                                        }

                                        $this->coordinateDestination->colony->save();
                                        $this->player->activeColony->save();
                                        $outComeMilitaries = $this->attackMilitaries-$attackerLoosing;
                                        if($outComeMilitaries < 0)
                                            $outComeMilitaries = 0;
                                        $attackLog = new GateFight;
                                        $attackLog->player_id_source = $this->player->id;
                                        $attackLog->colony_id_source = $this->player->activeColony->id;
                                        $attackLog->player_id_dest = $this->coordinateDestination->colony->player->id;
                                        $attackLog->colony_id_dest = $this->coordinateDestination->colony->id;
                                        $attackLog->military_source = $this->attackMilitaries;
                                        $attackLog->military_dest = $defenceMilitary;
                                        $attackLog->military_outcome = $outComeMilitaries;
                                        $attackLog->military_stolen = $stolenMilitaries;

                                        if($winState)
                                            $attackLog->player_id_winner = $attackLog->player_id_source;
                                        else
                                            $attackLog->player_id_winner = $attackLog->player_id_dest;

                                        foreach(config('stargate.resources') as $resource)
                                        {
                                            if(${$resource} >= 0)
                                                $attackLog->{$resource} = ${$resource};
                                        }
                                        $attackLog->save();

                                        if($winState)
                                        {
                                            $attackerReportString = trans('stargate.attackerWinReport', [
                                                'destination' => $destCoordinates,
                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                'player' => $this->coordinateDestination->colony->player->user_name,
                                                'loostTroops' => $attackerLooseString,
                                                'raidReward' => $attackerWinString,
                                            ], $this->player->lang);

                                            $defenderReportString = trans('stargate.defenderLostReport', [
                                                'destination' => $destCoordinates,
                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                'player' => $this->coordinateDestination->colony->player->user_name,
                                                'sourcePLanet' => $this->player->activeColony->name,
                                                'sourceDestination' => $sourceCoordinates,
                                                'sourcePlayer' => $this->player->user_name,
                                                'estimatedAttackTroops' => $estimatedAttackTroops,
                                                'loostTroops' => $defenderLooseString,
                                            ], $this->coordinateDestination->colony->player->lang);
                                        }
                                        else
                                        {
                                            $attackerReportString = trans('stargate.attackerLostReport', [
                                                'destination' => $destCoordinates,
                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                'player' => $this->coordinateDestination->colony->player->user_name,
                                                'loostTroops' => $attackerLooseString,
                                            ], $this->player->lang);

                                            $defenderReportString = trans('stargate.defenderWinReport', [
                                                'destination' => $destCoordinates,
                                                'planetDest' => $this->coordinateDestination->colony->name,
                                                'player' => $this->coordinateDestination->colony->player->user_name,
                                                'sourcePLanet' => $this->player->activeColony->name,
                                                'sourceDestination' => $sourceCoordinates,
                                                'sourcePlayer' => $this->player->user_name,
                                                'loostTroops' => $defenderLooseString,
                                                'estimatedAttackTroops' => $estimatedAttackTroops,
                                                'raidReward' => $defenderWinString,
                                            ], $this->coordinateDestination->colony->player->lang);
                                        }

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                            ],
                                            //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/incoming.gif'],
                                            "title" => "Stargate",
                                            "description" => $attackerReportString,
                                            'fields' => [
                                            ],
                                            'footer' => array(
                                                'text'  => 'Stargate',
                                            ),
                                        ];

                                        $reminder = new Reminder;
                                        $reminder->reminder_date = Carbon::now()->add('1s');
                                        $reminder->embed = json_encode($embed);
                                        $reminder->player_id = $this->player->id;
                                        $reminder->save();

                                        $embed = [
                                            'author' => [
                                                'name' => $this->player->user_name,
                                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                            ],
                                            'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/incoming.gif'],
                                            "title" => "Stargate",
                                            "description" => $defenderReportString,
                                            'fields' => [
                                            ],
                                            'footer' => array(
                                                'text'  => 'Stargate',
                                            ),
                                        ];

                                        $reminder = new Reminder;
                                        $reminder->reminder_date = Carbon::now()->add('1s');
                                        $reminder->embed = json_encode($embed);
                                        $reminder->player_id = $this->coordinateDestination->colony->player->id;
                                        $reminder->save();

                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }

                                    $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

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
        //0.03 * system
        //3 * galaxy

        if($source->galaxy != $destination->galaxy)
            return abs($source->galaxy - $destination->galaxy)*1;
        elseif($source->system != $destination->system)
        {
            $sysDiff = abs($source->system - $destination->system);
            if($sysDiff >= 0 && $sysDiff <= 5)
                return 0.04;
            elseif($sysDiff >= 0 && $sysDiff <= 5)
                return 0.04;
            elseif($sysDiff >= 6 && $sysDiff <= 10)
                return 0.08;
            elseif($sysDiff >= 11 && $sysDiff <= 20)
                return 0.16;
            elseif($sysDiff >= 21 && $sysDiff <= 50)
                return 0.32;
            elseif($sysDiff >= 51 && $sysDiff <= 10000)
                return 0.5;
        }
        else
            return 0.02;
    }

    public function canAttack($colonySource,$colonyDest)
    {
        $now = Carbon::now();

        $last96to120h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('120h')],['created_at', '<', Carbon::now()->sub('96h')]])->count();
        $last72to96h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('96h')],['created_at', '<', Carbon::now()->sub('72h')]])->count();
        $last48to72h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('72h')],['created_at', '<', Carbon::now()->sub('48h')]])->count();
        $last24to48h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('48h')],['created_at', '<', Carbon::now()->sub('24h')]])->count();
        $last0to24h = GateFight::Where([['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('24h')]])->count();
        $last0to24hGate = GateFight::Where([['type','gate'],['player_id_source',$colonySource->player->id],['player_id_dest',$colonyDest->player->id],['created_at', '>=', Carbon::now()->sub('24h')]])->count();

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
        elseif(($last0to24h >= 3 && $last24to48h > 0) || ($last0to24h >= 3 && $last48to72h > 0) || ($last0to24h >= 3 && $last72to96h > 0))
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
        elseif($last0to24hGate >= 2 || $last0to24h >= 3)
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
        else
        {
            //CHECK SI FIGHT SUR LA COLO CES DERNIERES 24H
            $lastColonyFight = GateFight::Where([['type','gate'],['player_id_source',$colonySource->player->id],['colony_id_dest',$colonyDest->id],['created_at', '>=', Carbon::now()->sub('24h')]])->orderBy('created_at','DESC')->get();
            if($lastColonyFight->count() > 0)
            {
                $now = Carbon::now();
                $convertedDate = Carbon::createFromFormat("Y-m-d H:i:s",$lastColonyFight->first()->created_at);
                $timeUntilAttack = $now->diffForHumans($convertedDate->addHours(24),[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
                return array('result' => false, 'message' => trans('stargate.AttackLimit', ['time' => $timeUntilAttack], $this->player->lang));
            }
        }
        return array('result' => true);
    }

    public function getPageExplo()
    {
        $displayList = $this->player->explorations->skip(10*($this->page -1))->take(10);

        $exploList = '';
        //SOURCE -
        $exploList .= "DATE - DESTINATION - REWARD\n";
        foreach($displayList as $exploration)
        {
            $explorationDate = substr($exploration->created_at,0,10);
            if(is_null($exploration->exploration_result))
                $reward = trans('stargate.explorationOngoing', [], $this->player->lang);
            elseif(!$exploration->exploration_result)
                $reward = "FAILED";
            else
                $reward = str_replace('Unit','Craft',$exploration->exploration_outcome);
            // ".$exploration->colonySource->name." [".$exploration->colonySource->coordinates->humanCoordinates()."] -
            $exploList .= $explorationDate." - [".$exploration->coordinateDestination->humanCoordinates()."] - ".$reward."\n";
        }

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('stargate.explorationList', [], $this->player->lang),
            "description" => $exploList,
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        return $embed;
    }
}
