<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\myDiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;
use App\Player;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use App\Coordinate;
use App\Fleet;

class Empire extends CommandHandler implements CommandInterface
{
    public $listner;
    public $paginatorMessage;
    public $maxTime;
    public $closed;

    public function execute()
    {
        if(!is_null($this->player))
        {
            try{
                echo PHP_EOL.'Execute Empire';
                if($this->player->ban)
                    return trans('generic.banned', [], $this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage', [], $this->player->lang);

                if(is_null($this->player->premium_expiration))
                    return trans('premium.restrictedCommand', [], $this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $prefix = $this->discord->commandClientOptions['prefix'];
                if(!is_null($this->message->channel->guild_id))
                {
                    $guildConfig = config('stargate.guilds.'.$this->message->channel->guild_id);
                    if(!is_null($guildConfig))
                        $prefix = $guildConfig['prefix'];
                }

                $warning = '';
                $incomingFleets = Fleet::Where([['player_source_id','!=',$this->player->id],['player_destination_id',$this->player->id],['fleets.returning', false],['fleets.ended', false],['fleets.mission', '!=' , 'scavenge']])->count();
                if($incomingFleets > 0)
                    $warning = "\n\n".trans('colony.incomingFleetWarning', [], $this->player->lang)."\n\n";

                if(empty($this->args))
                {
                    return $this->message->reply("`{$prefix}empire [production/buildings/activities/fleet/artifacts]`");
                }
                elseif(Str::startsWith('production', $this->args[0]))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        "title" => 'Empire',
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    foreach (config('stargate.resources') as $resource)
                    {
                        ${$resource.'TotalProduction'} = 0;
                        ${$resource.'Total'} = 0;
                    }
                    ${'militaryTotalProduction'} = 0;
                    ${'militaryTotal'} = 0;
                    ${'e2pzTotalProduction'} = 0;
                    ${'e2pzTotal'} = 0;

                    foreach($this->player->colonies as $key => $colony)
                    {
                        $colony->checkColony();

                        $colonyName = ' n° '.($key+1).': ';
                        if($this->player->hide_coordinates)
                            $colonyName .= '['.trans('generic.hidden', [], $this->player->lang).']';
                        else
                            $colonyName .= $colony->name.' ['.$colony->coordinates->humanCoordinates().']';

                        $colonyString = "";

                        $resourcesValue = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if(!empty($resourcesValue))
                                $resourcesValue .= "\n";

                            ${$resource.'TotalProduction'} += $colony['production_'.$resource];
                            ${$resource.'Total'} += $colony->$resource;
                            $resourcesValue .= config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($colony->$resource)." (".number_format($colony['production_'.$resource])."/h)";
                        }

                        if(!empty($resourcesValue))
                        {
                            $resourcesValue .= "\n".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang).": ".number_format($colony->energy_max - round($colony->energy_used)).' / '.number_format($colony->energy_max);

                            ${'militaryTotalProduction'} += $colony->production_military;
                            ${'e2pzTotalProduction'} += $colony->production_e2pz;
                            ${'militaryTotal'} += $colony->military;
                            ${'e2pzTotal'} += $colony->E2PZ;
                            $resourcesValue .= "\n".config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format(floor($colony->military))." (".number_format($colony->production_military)."/h)";
                            $resourcesValue .= "\n".config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format($colony->E2PZ,2)." (".number_format($colony->production_e2pz)."/w)";
                        }

                        $colonyString .= $resourcesValue;

                        $embed['fields'][] = array(
                            'name' => $colonyName,
                            'value' => $colonyString."\n.",
                            'inline' => true
                        );
                    }

                    $totalHourlyProdString = "";
                    $totalDailyProdString = "";
                    $totalResourcesString = "";
                    foreach (config('stargate.resources') as $resource)
                    {
                        $totalProd = ${$resource.'TotalProduction'};
                        $totalRes = ${$resource.'Total'};
                        $totalResourcesString .= "\n".config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($totalRes);
                        $totalHourlyProdString .= "\n".config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($totalProd)."/h";
                        $totalDailyProdString .= "\n".config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($totalProd*24)."/d";
                    }

                    $totalMilProd = ${'militaryTotalProduction'};
                    $totalResourcesString .= "\n".config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format(${'militaryTotal'});
                    $totalHourlyProdString .= "\n".config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format($totalMilProd)."/h";
                    $totalDailyProdString .= "\n".config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format($totalMilProd*24)."/d";

                    $totalE2pzProd = ${'e2pzTotalProduction'};
                    $totalResourcesString .= "\n".config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format(${'e2pzTotal'},2);
                    $totalHourlyProdString .= "\n".config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format((($totalE2pzProd / 10080) * 60),2)."/h";
                    $totalDailyProdString .= "\n".config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format(($totalE2pzProd / 10080)*1440,2)."/d";

                    $embed['fields'][] = array(
                        'name' => 'Total',
                        'value' => $totalResourcesString,
                        'inline' => true
                    );
                    $embed['fields'][] = array(
                        'name' => 'Hourly',
                        'value' => $totalHourlyProdString,
                        'inline' => true
                    );
                    $embed['fields'][] = array(
                        'name' => 'Daily',
                        'value' => $totalDailyProdString,
                        'inline' => true
                    );
                }
                elseif(Str::startsWith('buildings', $this->args[0]))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        "title" => 'Empire - '.trans('generic.buildings', [], $this->player->lang),
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    foreach($this->player->colonies as $key => $colony)
                    {
                        $colony->checkColony();

                        $colonyName = ' n° '.($key+1).': ';
                        if($this->player->hide_coordinates)
                            $colonyName .= '['.trans('generic.hidden', [], $this->player->lang).']';
                        else
                            $colonyName .= $colony->name.' ['.$colony->coordinates->humanCoordinates().']';

                        $colonyString = trans('generic.buildingSpace', [], $this->player->lang).": ".($colony->space_max - $colony->space_used).' / '.$colony->space_max."\n";
                        foreach($colony->buildings as $building)
                        {
                            $colonyString .= 'Lvl '.$building->pivot->level.' - '.trans('building.'.$building->slug.'.name', [], $this->player->lang)."\n";
                        }
                        if(empty($colonyString))
                            $colonyString = trans('generic.empty', [], $this->player->lang);
                        $embed['fields'][] = array(
                            'name' => $colonyName,
                            'value' => $colonyString."\n.",
                            'inline' => true
                        );
                    }
                }
                elseif(Str::startsWith('activities', $this->args[0]))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        "title" => 'Empire - '.trans('generic.activities', [], $this->player->lang),
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    foreach($this->player->colonies as $key => $colony)
                    {
                        $colony->checkColony();

                        $colonyName = ' n° '.($key+1).': ';
                        if($this->player->hide_coordinates)
                            $colonyName .= '['.trans('generic.hidden', [], $this->player->lang).']';
                        else
                            $colonyName .= $colony->name.' ['.$colony->coordinates->humanCoordinates().']';

                        $colonyString = '';
                        $now = Carbon::now();
                        if(!is_null($colony->active_building_end)){
                            $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$colony->active_building_end);
                            $buildingTime = $now->diffForHumans($buildingEnd,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                            $currentLevel = $colony->hasBuilding($colony->activeBuilding);

                            if(!$currentLevel)
                                $currentLevel = 0;
                            $colonyString .= "\n".trans('colony.buildingUnderConstruction', [], $this->player->lang)."\n"."Lvl ".($currentLevel+1)." - ".trans('building.'.$colony->activeBuilding->slug.'.name', [], $this->player->lang)."\n".$buildingTime."\n";

                            $buildingQueueString = "";
                            $queueIndex = 1;
                            foreach($colony->buildingQueue as $queuedBuilding)
                            {
                                $buildingQueueString .= "\n".$queueIndex.'. Lvl '.$queuedBuilding->pivot->level.' - '.trans('building.'.$queuedBuilding->slug.'.name', [], $this->player->lang);
                                $queueIndex++;
                            }
                            if(!empty($buildingQueueString))
                                $colonyString .= "\n".trans('building.queueList',[],$this->player->lang).$buildingQueueString."\n";

                        }

                        if($colony->craftQueues->count() > 0){
                            $queuedUnits = $colony->craftQueues()->limit(1)->get();
                            foreach($queuedUnits as $queuedUnit)
                            {
                                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedUnit->pivot->craft_end);
                                $buildingTime = $now->diffForHumans($buildingEnd,[
                                    'parts' => 3,
                                    'short' => true, // short syntax as per current locale
                                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                ]);
                                $colonyString .= "\n".trans('colony.craftQueue', [], $this->player->lang)."\n".trans('craft.'.$queuedUnit->slug.'.name', [], $this->player->lang)." - ".$buildingTime."\n";
                            }
                        }

                        if($colony->defenceQueues->count() > 0){
                            $queuedShips = $colony->shipQueues()->limit(1)->get();
                            foreach($queuedShips as $queuedShip)
                            {
                                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedShip->pivot->ship_end);
                                $buildingTime = $now->diffForHumans($buildingEnd,[
                                    'parts' => 3,
                                    'short' => true, // short syntax as per current locale
                                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                ]);
                                $colonyString .= "\n".trans('colony.shipQueue', [], $this->player->lang)."\n".$queuedShip->name." - ".$buildingTime."\n";
                            }
                        }

                        if($colony->defenceQueues->count() > 0){
                            $queuedDefences = $colony->defenceQueues()->limit(1)->get();
                            foreach($queuedDefences as $queuedDefence)
                            {
                                $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedDefence->pivot->defence_end);
                                $buildingTime = $now->diffForHumans($buildingEnd,[
                                    'parts' => 3,
                                    'short' => true, // short syntax as per current locale
                                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                ]);
                                $colonyString .= "\n".trans('colony.defenceQueue', [], $this->player->lang)."\n".trans('defence.'.$queuedDefence->slug.'.name', [], $this->player->lang)." - ".$buildingTime."\n";
                            }
                        }

                        $embed['fields'][] = array(
                            'name' => $colonyName,
                            'value' => $colonyString."\n.",
                            'inline' => true
                        );
                    }

                    if(!is_null($this->player->active_technology_end)){
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->active_technology_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        $currentLevel = $this->player->hasTechnology($this->player->activeTechnology);
                        if(!$currentLevel)
                            $currentLevel = 0;
                        $embed['fields'][] = array(
                            'name' => trans('colony.technologyUnderResearch', [], $this->player->lang),
                            'value' => "Lvl ".($currentLevel+1)." - ".trans('research.'.$this->player->activetechnology->slug.'.name', [], $this->player->lang)."\n".$buildingTime,
                            'inline' => true
                        );
                    }
                }
                elseif(Str::startsWith('fleet', $this->args[0]))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        "title" => 'Empire - '.trans('stargate.fleet', [], $this->player->lang),
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    foreach($this->player->colonies as $key => $colony)
                    {
                        $colony->checkColony();

                        $colonyName = ' n° '.($key+1).': ';
                        if($this->player->hide_coordinates)
                            $colonyName .= '['.trans('generic.hidden', [], $this->player->lang).']';
                        else
                            $colonyName .= $colony->name.' ['.$colony->coordinates->humanCoordinates().']';

                        $fleetString = "";
                        $defenseString = "";

                        foreach($colony->defences as $defense)
                            $defenseString .= $defense->pivot->number.' '.$defense->name."\n";

                        if(empty($defenseString))
                            $defenseString = trans('stargate.emptydefences', [], $this->player->lang);

                        foreach($colony->ships as $ship)
                            $fleetString .= $ship->pivot->number.' '.$ship->name."\n";

                        if(empty($fleetString))
                            $fleetString = trans('stargate.emptyFleet', [], $this->player->lang);

                        $embed['fields'][] = array(
                            'name' => $colonyName,
                            'value' => "**".trans('stargate.fleet', [], $this->player->lang)."**\n".$fleetString."\n\n**".trans('stargate.defences', [], $this->player->lang)."**\n".$defenseString,
                            'inline' => true
                        );
                    }
                }
                elseif(Str::startsWith('artifacts', $this->args[0]))
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        "title" => 'Empire - '.trans('generic.artifacts', [], $this->player->lang),
                        'fields' => [],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    foreach($this->player->colonies as $key => $colony)
                    {
                        $colony->checkColony();

                        $colonyName = ' n° '.($key+1).': ';
                        if($this->player->hide_coordinates)
                            $colonyName .= '['.trans('generic.hidden', [], $this->player->lang).']';
                        else
                            $colonyName .= $colony->name.' ['.$colony->coordinates->humanCoordinates().']';

                        $artifactString = "";
                        foreach($colony->artifacts as $artifact)
                            $artifactString .= $artifact->toString($this->player->lang)."\n";

                        if(empty($artifactString))
                            $artifactString = trans('generic.noArtifact', [], $this->player->lang);

                        $embed['fields'][] = array(
                            'name' => $colonyName,
                            'value' => $artifactString,
                            'inline' => true
                        );
                    }
                }
                else
                    return $this->message->reply("`{$prefix}empire [production/buildings/activities/fleet/artifacts]`");

                if(isset($embed))
                {
                    if(!empty($warning))
                        $embed['description'] = $warning;

                    $this->closed = false;
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $embed)->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;

                        $this->paginatorMessage->react(config('stargate.emotes.cancel'));

                        $filter = function($messageReaction){
                            if($messageReaction->user_id != $this->player->user_id || $this->closed == true)
                                return false;

                            if($messageReaction->user_id == $this->player->user_id)
                            {
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closed', [], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                        $this->closed = true;
                                    }
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
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }
}
