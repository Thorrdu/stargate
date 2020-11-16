<?php

namespace App\Utility;

use App\Reminder;
use App\SpyLog;
use App\Technology;
use Carbon\Carbon;

class PlayerUtility
{
    public static function spy($colonySource,$colonyDest){
        try{
            $sourceCoordinates = $colonySource->coordinates->humanCoordinates();
            $destCoordinates = $colonyDest->coordinates->humanCoordinates();

            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->reminder = trans('stargate.messageSpied', ['planetName' => $colonyDest->name, 'coordinate' => $destCoordinates, 'planetSource' => $colonySource->name, 'sourceCoordinates' => $sourceCoordinates], $colonySource->player->lang);
            $reminder->player_id = $colonyDest->player->id;
            $reminder->save();

            $spyLog = new SpyLog();
            $spyLog->source_player_id = $colonySource->player->id;
            $spyLog->colony_source_id = $colonySource->id;
            $spyLog->dest_player_id = $colonyDest->player->id;
            $spyLog->colony_destination_id = $colonyDest->id;
            $spyLog->save();

            $spy = Technology::where('slug', 'spy')->first();
            $counterSpy = Technology::where('slug', 'counterspy')->first();

            $playerDestination = $colonyDest->player;
            $spyLvl = $colonySource->player->hasTechnology($spy);
            $counterSpyLvl = $playerDestination->hasTechnology($counterSpy);

            if(!$spyLvl)
                $spyLvl = 0;
            if(!$counterSpyLvl)
                $counterSpyLvl = 0;

            $embed = [
                'author' => [
                    'name' => $colonySource->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpScreen.jpg'],
                "title" => "Stargate",
                "description" => trans('stargate.spyReportDescription', ['coordinateDestination' => $destCoordinates, 'planetDest' => $colonyDest->name, 'player' => $colonyDest->player->user_name], $colonySource->player->lang),
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];

            $showResources = $showFleet = $showdefences = $showBuildings = $showMilitaries = $showTechnologies = false;

            if($spyLvl < $counterSpyLvl && ($counterSpyLvl-$spyLvl) >= 4)
            {
                $embed['fields'][] = [
                    'name' => trans('stargate.emptyReportTitle', [], $colonySource->player->lang),
                    'value' => trans('stargate.technologyTooLow', [], $colonySource->player->lang),
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
                    $showdefences = true;
                if(($spyLvl-$counterSpyLvl) >= 3)
                    $showBuildings = $showMilitaries = true;
                if(($spyLvl-$counterSpyLvl) >= 4)
                    $showTechnologies = true;
            }

            if($showResources)
            {
                $resourceString = "";
                foreach(config('stargate.resources') as $resource){
                    $resourceString .= config('stargate.emotes.'.strtolower($resource)).' '.ucfirst($resource).": ".number_format($colonyDest->$resource).' ';
                }

                $embed['fields'][] = [
                    'name' => trans('generic.resources', [], $colonySource->player->lang),
                    'value' => $resourceString
                ];
            }

            if($showFleet)
            {
                if(count($colonyDest->ships) > 0)
                {
                    $fleetString = '';
                    foreach($colonyDest->ships as $ship)
                    {
                        $fleetString .= number_format($ship->pivot->number).' x '.$ship->toStrting($colonySource->player->lang)."\n";
                    }
                    $embed['fields'][] = array(
                                            'name' => trans('stargate.fleet', [], $colonySource->player->lang),
                                            'value' => $fleetString,
                                        );
                }
                else
                {
                    $embed['fields'][] = [
                        'name' => trans('stargate.fleet', [], $colonySource->player->lang),
                        'value' => trans('stargate.emptyFleet', [], $colonySource->player->lang)
                    ];
                }
            }

            if($showdefences)
            {
                if(count($colonyDest->defences) > 0)
                {
                    $coef = $colonyDest->getArtifactBonus(['bonus_category' => 'DefenceLure']);

                    $defenceString = '';
                    foreach($colonyDest->defences as $defence)
                    {
                        $defenceString .= number_format($defence->pivot->number*$coef).' '.trans('defence.'.$defence->slug.'.name', [], $colonySource->player->lang)."\n";
                    }
                    $embed['fields'][] = array(
                                            'name' => trans('stargate.defences', [], $colonySource->player->lang),
                                            'value' => $defenceString,
                                        );
                }
                else
                {
                    $embed['fields'][] = [
                        'name' => trans('stargate.defences', [], $colonySource->player->lang),
                        'value' => trans('stargate.emptydefences', [], $colonySource->player->lang)
                    ];
                }
            }

            if($showBuildings)
            {
                $buildingString = "";
                foreach($colonyDest->buildings as $building)
                {
                    if(!empty($buildingString))
                        $buildingString .= ', ';
                    $buildingString .= trans('building.'.$building->slug.'.name', [], $colonySource->player->lang).' ('.$building->pivot->level.')';
                }
                if(empty($buildingString))
                    $buildingString = 'Aucun bâtiment';
                $embed['fields'][] = [
                    'name' => trans('stargate.buildings', [], $colonySource->player->lang),
                    'value' => $buildingString
                ];
            }

            if($showMilitaries)
            {
                $militaryString = config('stargate.emotes.military')." ".trans('generic.militaries', [], $colonySource->player->lang).": ".number_format($colonyDest->military);
                /*foreach($colonyDest->units as $unit)
                {
                    $militaryString .= ', ';
                    $militaryString .= trans('craft.'.$unit->slug.'.name', [], $colonySource->player->lang).' ('.number_format($unit->pivot->number).')';
                }*/
                $embed['fields'][] = [
                    'name' => trans('generic.militaries', [], $colonySource->player->lang),
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
                    $technologyString .= trans('research.'.$technology->slug.'.name', [], $colonySource->player->lang).' ('.$technology->pivot->level.')';
                }
                if(empty($technologyString))
                    $technologyString = "Aucune technologie";
                $embed['fields'][] = [
                    'name' => trans('generic.technologies', [], $colonySource->player->lang),
                    'value' => $technologyString
                ];
            }

            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->embed = json_encode($embed);
            $reminder->player_id = $colonySource->player->id;
            $reminder->save();

        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }
}
