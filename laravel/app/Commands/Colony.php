<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;
use App\Player;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use App\Coordinate;

class Colony extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {

            try{
                echo PHP_EOL.'Execute Colony';
                if($this->player->ban)
                    return trans('generic.banned',[],$this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $this->player->checkFleets();

                if(isset($this->args[0]) && Str::startsWith('list',$this->args[0]))
                {
                    $coloniesString = "";
                    $colonyIndex = 1;
                    foreach($this->player->colonies as $colony)
                    {
                        $coloniesString .= $colonyIndex.'. '.$colony->name." [".$colony->coordinates->humanCoordinates()."]\n";
                        $colonyIndex++;
                    }
                    return "\n__".trans('generic.colonies',[],$this->player->lang)."__\n".$coloniesString;
                }
                elseif(count($this->args) >= 2 && strlen($this->args[0]) > 2 && Str::startsWith('rename',$this->args[0]))
                {
                    if(is_null($this->player->premium_expiration))
                        return trans('premium.restrictedCommand', [], $this->player->lang);

                    $newColonyName = trim(join(' ', array_slice($this->args, 1)));

                    if(strlen($newColonyName) < 2)
                        return trans('generic.nameTooShort',[],$this->player->lang);

                    if(strlen($newColonyName) > 25)
                        return trans('generic.nameTooLong',[],$this->player->lang);

                    $this->player->activeColony->name = $newColonyName;
                    $this->player->activeColony->save();
                    return trans('colony.colonyNameChanged' , ['name' => $this->player->activeColony->name], $this->player->lang);
                }
                elseif(count($this->args) >= 2 && Str::startsWith('switch',$this->args[0]))
                {
                    if(preg_match('/(([0-9]{1,}:[0-9]{1,}:[0-9]{1,})|([0-9]{1,};[0-9]{1,};[0-9]{1,}))/', $this->args[1], $coordinatesMatch))
                    {
                        //Est-ce que la destination à une porte ?
                        if(strstr($coordinatesMatch[0],';'))
                            $coordinates = explode(';',$coordinatesMatch[0]);
                        else
                            $coordinates = explode(':',$coordinatesMatch[0]);

                        $coordinateSwitch = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();
                        if(!is_null($coordinateSwitch))
                        {
                            if(!is_null($coordinateSwitch->colony) && $coordinateSwitch->colony->player->id == $this->player->id)
                            {
                                $this->player->active_colony_id = $coordinateSwitch->colony->id;
                                $this->player->save();
                                return trans('colony.colonySwitched', ['colony' => $coordinateSwitch->colony->name.' ['.$coordinateSwitch->humanCoordinates().']'], $this->player->lang);
                            }
                            else
                                return trans('colony.UnknownColony', [], $this->player->lang);
                        }
                        else
                            return trans('colony.UnknownColony', [], $this->player->lang);
                    }
                    elseif((int)$this->args[1] > 0 && (int)$this->args[1] <= $this->player->colonies->count())
                    {
                        $this->player->active_colony_id = $this->player->colonies[(int)$this->args[1]-1]->id;
                        $this->player->save();
                        return trans('colony.colonySwitched', ['colony' => $this->player->colonies[(int)$this->args[1]-1]->name.' ['.$this->player->colonies[(int)$this->args[1]-1]->coordinates->humanCoordinates().']'], $this->player->lang);
                    }
                    else
                        return trans('colony.UnknownColony', [], $this->player->lang);
                }

                if(count($this->args) >= 2 && Str::startsWith('remove',$this->args[0]))
                {
                    if(preg_match('/(([0-9]{1,}:[0-9]{1,}:[0-9]{1,})|([0-9]{1,};[0-9]{1,};[0-9]{1,}))/', $this->args[1], $coordinatesMatch))
                    {
                        //Est-ce que la destination à une porte ?
                        if(strstr($coordinatesMatch[0],';'))
                            $coordinates = explode(';',$coordinatesMatch[0]);
                        else
                            $coordinates = explode(':',$coordinatesMatch[0]);

                        $coordinateSwitch = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();
                        if(!is_null($coordinateSwitch))
                        {
                            if(!is_null($coordinateSwitch->colony) && $coordinateSwitch->colony->player->id == $this->player->id)
                            {
                                if($this->player->colonies[0]->id == $coordinateSwitch->colony->id)
                                    return trans('colony.cannotRemoveHomePlanet', [], $this->player->lang);
                                else
                                {

                                    $colonyName = $coordinateSwitch->colony->name.' ['.$coordinateSwitch->humanCoordinates().']';
                                    $removeConfirm = trans('colony.removeRequest', ['name' => $colonyName], $this->player->lang);

                                    $this->maxTime = time()+180;
                                    $this->message->channel->sendMessage($removeConfirm)->then(function ($messageSent) use($coordinateSwitch){

                                        $this->closed = false;
                                        $this->paginatorMessage = $messageSent;
                                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                            });
                                        });

                                        $filter = function($messageReaction){
                                            return $messageReaction->user_id == $this->player->user_id;
                                        };
                                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($coordinateSwitch){
                                            $messageReaction = $collector->first();
                                            try{
                                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                                {
                                                    $this->player->removeColony($coordinateSwitch->colony);
                                                    $this->paginatorMessage->content = trans('colony.colonyRemoved', [], $this->player->lang);
                                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                }
                                                elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                                {
                                                    $this->paginatorMessage->content = trans('generic.cancelled', [], $this->player->lang);
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
                            }
                            else
                                return trans('colony.UnknownColony', [], $this->player->lang);
                        }
                        else
                            return trans('colony.UnknownColony', [], $this->player->lang);
                    }
                    elseif((int)$this->args[1] > 0 && (int)$this->args[1] <= $this->player->colonies->count())
                    {
                        $colonyAction = $this->args[1];
                        if($colonyAction == 1)
                            return trans('colony.cannotRemoveHomePlanet', [], $this->player->lang);
                        else
                        {
                            $colonyName = $this->player->colonies[(int)$this->args[1]-1]->name.' ['.$this->player->colonies[(int)$this->args[1]-1]->coordinates->humanCoordinates().']';
                            $removeConfirm = trans('colony.removeRequest', ['name' => $colonyName], $this->player->lang);

                            $this->maxTime = time()+180;
                            $this->message->channel->sendMessage($removeConfirm)->then(function ($messageSent){

                                $this->closed = false;
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
                                            $this->player->removeColony($this->player->colonies[(int)$this->args[1]-1]);
                                            $this->paginatorMessage->content = trans('colony.colonyRemoved', [], $this->player->lang);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                        }
                                        elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                        {
                                            $this->paginatorMessage->content = trans('generic.cancelled', [], $this->player->lang);
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

                    }
                    else
                        return trans('colony.UnknownColony', [], $this->player->lang);
                }

                $this->player->activeColony->checkColony();
                $this->player->refresh();

                $coordinates = $this->player->activeColony->coordinates;

                $embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                   // 'thumbnail' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/planets/'.$this->player->activeColony->image],
                    "title" => 'Colonie '.$this->player->activeColony->name,
                    "description" => trans('generic.coordinates', [], $this->player->lang).": ".$coordinates->humanCoordinates(),
                    'fields' => [],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];

                $resourcesValue = "";
                $storageValue = "";
                foreach (config('stargate.resources') as $resource)
                {
                    if(!empty($resourcesValue))
                        $resourcesValue .= "\n";

                    $resourcesValue .= config('stargate.emotes.'.$resource).' '.ucfirst($resource).": ".number_format($this->player->activeColony->$resource)." (".number_format($this->player->activeColony['production_'.$resource])."/h)";
                    $storageValue .= number_format($this->player->activeColony->{'storage_'.$resource}).' '.ucfirst($resource)."\n";
                }

                if(!empty($resourcesValue))
                {
                    $resourcesValue .= "\n".config('stargate.emotes.energy')." ".trans('generic.energy', [], $this->player->lang).": ".number_format($this->player->activeColony->energy_max - round($this->player->activeColony->energy_used)).' / '.number_format($this->player->activeColony->energy_max);
                    $resourcesValue .= "\n".config('stargate.emotes.military')." ".trans('generic.militaries', [], $this->player->lang).": ".number_format(floor($this->player->activeColony->military))." (".number_format($this->player->activeColony->production_military)."/h)";
                    $resourcesValue .= "\n".config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).": ".number_format($this->player->activeColony->E2PZ,2)." (".number_format($this->player->activeColony->production_e2pz)."/w)";
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.production')." ".trans('generic.resources', [], $this->player->lang),
                                            'value' => $resourcesValue,
                                            'inline' => true
                                        );
                }


                $prodBuildings = $this->player->activeColony->buildings->filter(function ($value) {
                    return $value->type == 'Production' || $value->type == "Energy";
                });
                $prodBuildingsValue = "";
                foreach($prodBuildings as $prodBuilding)
                {
                    if(!empty($prodBuildingsValue))
                        $prodBuildingsValue .= "\n";
                    $prodBuildingsValue .= 'Lvl '.$prodBuilding->pivot->level.' - '.trans('building.'.$prodBuilding->slug.'.name', [], $this->player->lang);
                }
                if(!empty($prodBuildingsValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.productionBuilding')." ".trans('generic.productionBuildings', [], $this->player->lang),
                                            'value' => $prodBuildingsValue,
                                            'inline' => true
                                        );
                }

                $scienceBuildings = $this->player->activeColony->buildings->filter(function ($value) {
                    return $value->type == "Science";
                });
                $scienceBuildingsValue = "";
                foreach($scienceBuildings as $scienceBuilding)
                {
                    if(!empty($scienceBuildingsValue))
                        $scienceBuildingsValue .= "\n";
                    $scienceBuildingsValue .= 'Lvl '.$scienceBuilding->pivot->level.' - '.trans('building.'.$scienceBuilding->slug.'.name', [], $this->player->lang);
                }
                if(!empty($scienceBuildingsValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.researchBuilding')." ".trans('generic.scienceBuildings', [], $this->player->lang),
                                            'value' => $scienceBuildingsValue,
                                            'inline' => true
                                        );
                }

                $militaryBuildings = $this->player->activeColony->buildings->filter(function ($value) {
                    return $value->type == "Military";
                });
                $militaryBuildingsValue = "";
                foreach($militaryBuildings as $militaryBuilding)
                {
                    if(!empty($militaryBuildingsValue))
                        $militaryBuildingsValue .= "\n";
                    $militaryBuildingsValue .= 'Lvl '.$militaryBuilding->pivot->level.' - '.trans('building.'.$militaryBuilding->slug.'.name', [], $this->player->lang);
                }
                if(!empty($militaryBuildingsValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.military')." ".trans('generic.militaryBuildings', [], $this->player->lang),
                                            'value' => $militaryBuildingsValue,
                                            'inline' => true
                                        );
                }

                if(!empty($storageValue))
                {
                    $storageValue = "\n".trans('generic.buildingSpace', [], $this->player->lang).": ".($this->player->activeColony->space_max - $this->player->activeColony->space_used).' / '.$this->player->activeColony->space_max."\n".$storageValue;

                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.storage')." ".trans('generic.storageCapacity', [], $this->player->lang),
                                            'value' => $storageValue,
                                            'inline' => true
                                        );
                }

                $technologyLaboValue = "";
                $technologyCenterValue = "";

                foreach($this->player->technologies as $technology)
                {
                    if($technology->type == "Labo")
                    {
                        if(!empty($technologyLaboValue))
                            $technologyLaboValue .= "\n";
                        $technologyLaboValue .= 'Lvl '.$technology->pivot->level.' - '.trans('research.'.$technology->slug.'.name', [], $this->player->lang);
                    }
                    else
                    {
                        if(!empty($technologyCenterValue))
                            $technologyCenterValue .= "\n";
                        $technologyCenterValue .= 'Lvl '.$technology->pivot->level.' - '.trans('research.'.$technology->slug.'.name', [], $this->player->lang);
                    }

                }
                if(!empty($technologyLaboValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.research')." ".trans('generic.technologies', [], $this->player->lang)." ".trans('generic.laboratory', [], $this->player->lang),
                                            'value' => $technologyLaboValue,
                                            'inline' => true
                                        );
                }
                if(!empty($technologyCenterValue))
                {
                    $embed['fields'][] = array(
                                            'name' => config('stargate.emotes.research')." ".trans('generic.technologies', [], $this->player->lang)." ".trans('generic.ships', [], $this->player->lang),
                                            'value' => $technologyCenterValue,
                                            'inline' => true
                                        );
                }


                $artifactString = "";
                foreach($this->player->activeColony->artifacts as $artifact)
                {
                    $artifactString .= $artifact->toString($this->player->lang)."\n";
                }
                if(!empty($artifactString))
                {
                    $embed['fields'][] = array(
                                            'name' => trans('generic.artifacts', [], $this->player->lang),
                                            'value' => $artifactString,
                                            'inline' => true
                                        );
                }

                if(count($this->player->activeColony->units) > 0)
                {
                    $unitsString = '';
                    foreach($this->player->activeColony->units as $unit)
                    {
                        $unitsString .= number_format($unit->pivot->number).' '.trans('craft.'.$unit->slug.'.name', [], $this->player->lang)."\n";
                    }
                    $embed['fields'][] = array(
                                            'name' => trans('generic.units', [], $this->player->lang),
                                            'value' => $unitsString,
                                            'inline' => true
                                        );
                }

                if(count($this->player->activeColony->ships) > 0)
                {
                    $shipString = '';
                    foreach($this->player->activeColony->ships as $ship)
                    {
                        $shipString .= number_format($ship->pivot->number).' '.$ship->name."\n";
                    }
                    $embed['fields'][] = array(
                                            'name' => trans('generic.ships', [], $this->player->lang),
                                            'value' => $shipString,
                                            'inline' => true
                                        );
                }

                if(count($this->player->activeColony->defences) > 0)
                {
                    $defenceString = '';
                    foreach($this->player->activeColony->defences as $defence)
                    {
                        $defenceString .= number_format($defence->pivot->number).' '.trans('defence.'.$defence->slug.'.name', [], $this->player->lang)."\n";
                    }
                    $embed['fields'][] = array(
                                            'name' => trans('generic.defences', [], $this->player->lang),
                                            'value' => $defenceString,
                                            'inline' => true
                                        );
                }

                $now = Carbon::now();
                if(!is_null($this->player->activeColony->active_building_end)){
                    $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->active_building_end);
                    $buildingTime = $now->diffForHumans($buildingEnd,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $currentLevel = $this->player->activeColony->hasBuilding($this->player->activeColony->activeBuilding);
                    if(!$currentLevel)
                        $wantedLvl = 1;
                    elseif($this->player->activeColony->active_building_remove)
                        $wantedLvl = $currentLevel - 1;
                    else
                        $wantedLvl = $currentLevel + 1;

                    $embed['fields'][] = array(
                        'name' => trans('colony.buildingUnderConstruction', [], $this->player->lang),
                        'value' => "Lvl ".$wantedLvl." - ".trans('building.'.$this->player->activeColony->activeBuilding->slug.'.name', [], $this->player->lang)."\n".$buildingTime,
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

                if($this->player->activeColony->craftQueues->count() > 0){
                    $queueString = "";
                    $queuedUnits = $this->player->activeColony->craftQueues()->limit(5)->get();
                    foreach($queuedUnits as $queuedUnit)
                    {
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedUnit->pivot->craft_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= trans('craft.'.$queuedUnit->slug.'.name', [], $this->player->lang)." - ".$buildingTime."\n";
                    }
                    if($this->player->activeColony->craftQueues->count() > 5)
                    {
                        $lastQueue = $this->player->activeColony->craftQueues()->where('craft_end','>',Carbon::now())->orderBy('craft_end', 'DESC')->first();
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->craft_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= "... - ".$buildingTime."\n";
                    }

                    $embed['fields'][] = array(
                        'name' => trans('colony.craftQueue', [], $this->player->lang),
                        'value' => $queueString,
                        'inline' => true
                    );
                }

                if($this->player->activeColony->shipQueues->count() > 0){
                    $queueString = "";
                    $queuedShips = $this->player->activeColony->shipQueues()->limit(5)->get();
                    foreach($queuedShips as $queuedShip)
                    {
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedShip->pivot->ship_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= $queuedShip->name." - ".$buildingTime."\n";
                    }
                    if($this->player->activeColony->shipQueues->count() > 5)
                    {
                        $lastQueue = $this->player->activeColony->shipQueues()->where('ship_end','>',Carbon::now())->orderBy('ship_end', 'DESC')->first();
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->ship_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= "... - ".$buildingTime."\n";
                    }

                    $embed['fields'][] = array(
                        'name' => trans('colony.ShipQueue', [], $this->player->lang),
                        'value' => $queueString,
                        'inline' => true
                    );
                }

                if($this->player->activeColony->defenceQueues->count() > 0){
                    $queueString = "";
                    $queuedDefences = $this->player->activeColony->defenceQueues()->limit(5)->get();
                    foreach($queuedDefences as $queuedDefence)
                    {
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$queuedDefence->pivot->defence_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= trans('defence.'.$queuedDefence->slug.'.name', [], $this->player->lang)." - ".$buildingTime."\n";
                    }
                    if($this->player->activeColony->defenceQueues->count() > 5)
                    {
                        $lastQueue = $this->player->activeColony->defenceQueues()->where('defence_end','>',Carbon::now())->orderBy('defence_end', 'DESC')->first();
                        $buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->defence_end);
                        $buildingTime = $now->diffForHumans($buildingEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $queueString .= "... - ".$buildingTime."\n";
                    }

                    $embed['fields'][] = array(
                        'name' => trans('colony.defenceQueue', [], $this->player->lang),
                        'value' => $queueString,
                        'inline' => true
                    );
                }

                $this->message->channel->sendMessage('', false, $embed);

            }
            catch(\Exception $e)
            {
                return $e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }
}
