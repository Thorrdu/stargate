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

class Dakara extends CommandHandler implements CommandInterface
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
                echo PHP_EOL.'Execute Dakara';

                if($this->player->ban)
                    return trans('generic.banned', [], $this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $researchCenter = Building::find(7);
                $centerLevel = $this->player->activeColony->hasBuilding($researchCenter);
                $dakara = Building::find(21);
                $dakaraLevel = $this->player->activeColony->hasBuilding($dakara);
                if((!$dakaraLevel || $dakaraLevel < 2) || (!$centerLevel || $centerLevel < 5) || ($this->player->activeColony->stargate_buried || ($this->player->activeColony->stargate_burying && !$this->player->activeColony->stargate_buried)))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/dakara-inactive.png'],
                        "title" => trans('building.dakara-super-weapon.name', [], $this->player->lang),
                        "description" => trans('dakara.inactive', [], $this->player->lang),
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

                if(empty($this->args))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/enabledStargate.jpg'],
                        "title" => trans('building.dakara-super-weapon.name', [], $this->player->lang),
                        "description" => trans('dakara.howTo', [], $this->player->lang),
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

                if(!preg_match('/(([0-9]{1,}:[0-9]{1,}:[0-9]{1,})|([0-9]{1,};[0-9]{1,};[0-9]{1,}))/', $this->args[0], $coordinatesMatch))
                {
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);
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

                if(!is_null($this->coordinateDestination->colony) && !is_null($this->coordinateDestination->colony->player->vacation))
                    return trans('profile.playerVacation', [], $this->player->lang);

                if(!is_null($this->coordinateDestination->colony) && $this->coordinateDestination->colony->player->id == $this->player->id && $this->player->user_id != config('stargate.ownerId'))
                    return trans('stargate.samePlayerAction', [], $this->player->lang);

                if(is_null($this->coordinateDestination->colony))
                    return trans('stargate.neverExploredWorld', [], $this->player->lang);

                if(!$this->isAtRange($dakaraLevel, $this->player->activeColony->coordinates, $this->coordinateDestination))
                    return trans('dakara.notAtRange', [], $this->player->lang);

                //Check Consommation E2PZ
                $travelCost = $this->getConsumption($this->player->activeColony->coordinates,$this->coordinateDestination);
                if($this->player->activeColony->E2PZ < $travelCost)
                    return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang);

                if(!$this->player->isRaidable($this->coordinateDestination->colony->player) && $this->coordinateDestination->colony->player->npc == 0)
                    return trans('stargate.weakOrStrong', [], $this->player->lang);

                $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                if($raidCapability['result'] == false)
                    return $raidCapability['message'];

                $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                $destCoordinates = $this->coordinateDestination->humanCoordinates();
                $attackConfirmation = trans('dakara.attackConfirmation', ['planetName' => $this->coordinateDestination->colony->name, 'coordinateDestination' => $destCoordinates,'planetNameSource' => $this->player->activeColony->name, 'coordinateSource' => $sourceCoordinates, 'consumption' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost,3)], $this->player->lang);

                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/dakara-charging.png'],
                    "title" => trans('building.dakara-super-weapon.name', [], $this->player->lang),
                    "description" => $attackConfirmation,
                    'fields' => [
                    ],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];
                $newEmbed = $this->discord->factory(Embed::class,$embed);

                $this->maxTime = time()+180;
                $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent) use($travelCost,$dakara,$dakaraLevel){

                    $this->paginatorMessage = $messageSent;
                    $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                        $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                        });
                    });

                    $filter = function($messageReaction){
                        return $messageReaction->user_id == $this->player->user_id;
                    };
                    $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector) use($travelCost,$dakara,$dakaraLevel){
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
                                    $this->player->activeColony->refresh();

                                    $raidCapability = $this->canAttack($this->player->activeColony,$this->coordinateDestination->colony);
                                    $travelCost = $this->getConsumption($this->player->activeColony->coordinates,$this->coordinateDestination);

                                    if($raidCapability['result'] == false)
                                        $cancelReason = $raidCapability['message'];
                                    elseif($this->player->activeColony->E2PZ < $travelCost)
                                        $cancelReason = trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round(($travelCost-$this->player->activeColony->E2PZ),4)], $this->player->lang);
                                    elseif(!$this->isAtRange($dakaraLevel, $this->player->activeColony->coordinates, $this->coordinateDestination))
                                        $cancelReason = trans('stargate.notAtRange', [], $this->player->lang);

                                    if(!empty($cancelReason))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,[
                                            'title' => trans('generic.cancelled', [], $this->player->lang),
                                            'description' => $cancelReason
                                            ]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                        return;
                                    }

                                    $this->player->activeColony->E2PZ -= $travelCost;
                                    $this->player->activeColony->save();

                                    $sourceCoordinates = $this->player->activeColony->coordinates->humanCoordinates();
                                    $destCoordinates = $this->coordinateDestination->humanCoordinates();
                                    $attackSentMessage = trans('dakara.attackSent', ['planet' => $this->coordinateDestination->colony->name,'coordinateDestination' => $destCoordinates], $this->player->lang);

                                    $embed = [
                                        'author' => [
                                            'name' => $this->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                        ],
                                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/dakara-firing.gif'],
                                        "title" => trans('building.dakara-super-weapon.name', [], $this->player->lang),
                                        "description" => $attackSentMessage,
                                        'fields' => [
                                        ],
                                        'footer' => array(
                                            'text'  => 'Stargate',
                                        ),
                                    ];
                                    $newEmbed = $this->discord->factory(Embed::class,$embed);
                                    $messageReaction->message->addEmbed($newEmbed);

                                    $dakaraDestinationLevel = $this->coordinateDestination->colony->hasBuilding($dakara);
                                    if(!$dakaraDestinationLevel)
                                        $dakaraDestinationLevel = 0;

                                    $defenderLooseString = '';
                                    if($dakaraLevel > $dakaraDestinationLevel)
                                    {
                                        $dakaraDiff = $dakaraLevel - $dakaraDestinationLevel;
                                        if($dakaraDiff > 3)
                                            $dakaraDiff = 3;

                                        $newDefCoef = 1 - $dakaraDiff/10;
                                        $loosingCoef = 1 - $newDefCoef;
                                        /*
                                        //-10% def par lvl de diff
                                        */
                                        $ruinfield = ['iron' => 0, 'gold' => 0, 'quartz' => 0];
                                        foreach($this->coordinateDestination->colony->defences as $defence)
                                        {
                                            $lostDefenceQty = ceil($defence->pivot->number*$loosingCoef);
                                            $newDefenceQty = floor($defence->pivot->number*$newDefCoef);

                                            $defenderLooseString .= trans('defence.'.$defence->slug.'.name', [], $this->coordinateDestination->colony->player->lang).': '.number_format($lostDefenceQty)."\n";

                                            if($newDefenceQty > 0)
                                            {
                                                $defence->pivot->number = $newDefenceQty;
                                                $defence->pivot->save();
                                            }
                                            else
                                                $this->coordinateDestination->colony->defences()->detach($defence->id);

                                            if($defence->type == 'Space')
                                            {
                                                $defPrice = $defence->getPrice($lostDefenceQty);
                                                foreach(config('stargate.resources') as $resource)
                                                {
                                                    if($resource != 'naqahdah')
                                                        $ruinfield[$resource] += floor($defPrice[$resource]*0.75);
                                                }
                                            }
                                        }

                                        if($this->coordinateDestination->colony->production_military > 0)
                                        {
                                            $lostMilitaries = ceil($this->coordinateDestination->colony->production_military * $dakaraDiff);
                                            if(($this->coordinateDestination->colony->military - $lostMilitaries) < 0)
                                                $lostMilitaries = $this->coordinateDestination->colony->military;

                                            $this->coordinateDestination->colony->military = $this->coordinateDestination->colony->military - $lostMilitaries;

                                            $defenderLooseString .= config('stargate.emotes.military')." ".trans('generic.military', [], $this->coordinateDestination->colony->player->lang).': '.number_format($lostMilitaries)."\n";
                                        }

                                        $ruinFieldString = '';
                                        if($ruinfield['iron'] > 0)
                                        {
                                            foreach(config('stargate.resources') as $resource)
                                            {
                                                if($resource != 'naqahdah')
                                                {
                                                    $ruinFieldString .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format($ruinfield[$resource]);
                                                    $this->coordinateDestination->$resource += $ruinfield[$resource];
                                                }
                                            }
                                            $defenderLooseString .= "\n".trans('fleet.ruinFieldGenerated', ['resources' => $ruinFieldString], $this->coordinateDestination->colony->player->lang);
                                            $this->coordinateDestination->save();
                                        }
                                    }
                                    else
                                    {
                                        //NOTHING DESTROYED
                                        $defenderLooseString = trans('dakara.nothingLost', [], $this->coordinateDestination->colony->player->lang);
                                    }
                                    $this->coordinateDestination->save();
                                    $this->coordinateDestination->colony->save();

                                    $attackLog = new GateFight;
                                    $attackLog->player_id_source = $this->player->id;
                                    $attackLog->colony_id_source = $this->player->activeColony->id;
                                    $attackLog->player_id_dest = $this->coordinateDestination->colony->player->id;
                                    $attackLog->colony_id_dest = $this->coordinateDestination->colony->id;
                                    $attackLog->save();

                                    $defenderReportString = trans('dakara.defenderReport', [
                                        'destination' => $destCoordinates,
                                        'planetDest' => $this->coordinateDestination->colony->name,
                                        'player' => $this->coordinateDestination->colony->player->user_name,
                                        'sourcePLanet' => $this->player->activeColony->name,
                                        'sourceDestination' => $sourceCoordinates,
                                        'sourcePlayer' => $this->player->user_name,
                                        'loostTroops' => $defenderLooseString
                                    ], $this->coordinateDestination->colony->player->lang);

                                    $embed = [
                                        'author' => [
                                            'name' => $this->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                        ],
                                        'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/incoming.png'],
                                        "title" => trans('building.dakara-super-weapon.name', [], $this->player->lang),
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
            catch(\Exception $e)
            {
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }

    public function isAtRange(Int $level, Coordinate $source,Coordinate $destination)
    {
        $sysRange = pow(2,$level);
        $galaxRange = floor($sysRange/128);

        if($source->galaxy != $destination->galaxy && $galaxRange < abs($source->galaxy - $destination->galaxy))
            return false;
        elseif($source->system != $destination->system && $sysRange < abs($source->system - $destination->system))
            return false;
        else
            return true;
    }

    public function getConsumption(Coordinate $source,Coordinate $destination)
    {
        //0.03 * system
        //3 * galaxy

        if($source->galaxy != $destination->galaxy)
            return abs($source->galaxy - $destination->galaxy)*5;
        elseif($source->system != $destination->system)
        {
            $sysDiff = abs($source->system - $destination->system);
            if($sysDiff >= 0 && $sysDiff <= 5)
                return 0.35;
            elseif($sysDiff >= 6 && $sysDiff <= 10)
                return 0.9;
            elseif($sysDiff >= 11 && $sysDiff <= 20)
                return 1.8;
            elseif($sysDiff >= 21 && $sysDiff <= 50)
                return 3.3;
            elseif($sysDiff >= 51 && $sysDiff <= 10000)
                return 5;
        }
        else
            return 0.2;
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
}
