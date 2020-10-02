<?php

namespace App;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    public function ships(){
        return $this->belongsToMany('App\Ship')->withPivot('number');
    }

    public function sourceColony(){
        return $this->belongsTo('App\Colony','colony_source_id','id');
    }

    public function destinationColony(){
        return $this->belongsTo('App\Colony','colony_destination_id','id');
    }

    public function sourcePlayer(){
        return $this->belongsTo('App\Player','player_source_id','id');
    }

    public function destinationPlayer(){
        return $this->belongsTo('App\Player','player_destination_id','id');
    }



    public function getFleetTime(Coordinate $coordinateSource, Coordinate $coordionateDest, $speed)
    {
        //Speed 1 same sys = 7m + 30sec/pla (420 + 30/pla)
        //Speed 1 dif sys = 15m + 1m/sys (900 + 60/sys)
        //Speed 1 dif galax = 1h + 30m/galax + 30s/sys (3600 + 1800/galax + 30/sys)
        $galaxyDifference = abs($coordinateSource->galaxy - $coordionateDest->galaxy);
        $systemDifference = abs($coordinateSource->system - $coordionateDest->system);
        $planetDifference = abs($coordinateSource->planet - $coordionateDest->planet);

        $travelTime = 0;
        if($galaxyDifference > 0)
            $travelTime += 3600 + $galaxyDifference*1800 + $systemDifference*30;
        elseif($systemDifference > 0)
            $travelTime = 900 + $systemDifference * 60;
        else
            $travelTime = 420 + $planetDifference * 30;

        $travelTime /= $speed;
        $travelTime *= $coordinateSource->colony->player->getShipSpeedBonus();

        return $travelTime;
    }

    public function outcome()
    {
        $availableResources = config('stargate.resources');
        $availableResources[] = 'E2PZ';
        $availableResources[] = 'military';

        if($this->returning || $this->mission == 'base')
        {

            /*
            Retour de flotte de la planète [4:28:4]
            Une flotte est rentrée sur la planète Asgard [5:25:3],
            elle était partie sur la planète Colonie [4:28:4] du joueur Thorrdu.

            Elle était composée de :

            - vitevite : 1

            Elle a ramené :

            - Fer : 1
            - Hydrogène : 482
            - Militaires : 2

            */

            //Si vicoire fight, image de victoire
            if($this->returning){
                $sourceColony = $this->destinationColony;
                $destinationColony = $this->sourceColony;
                $sourceCoordinates = $this->destinationColony->coordinates->humanCoordinates();
                $destCoordinates = $this->sourceColony->coordinates->humanCoordinates();
            }
            else
            {
                $sourceColony = $this->sourceColony;
                $destinationColony = $this->destinationColony;
                $sourceCoordinates = $this->sourceColony->coordinates->humanCoordinates();
                $destCoordinates = $this->destinationColony->coordinates->humanCoordinates();
            }

            //Ressources -> colony
            foreach($availableResources as $availableResource)
            {
                if($this->$availableResource > 0)
                {
                    $destinationColony->$availableResource += $this->$availableResource;
                    if(isset($destinationColony->{'storage_'.$availableResource}) && $destinationColony->{'storage_'.$availableResource} < $destinationColony->$availableResource)
                        $destinationColony->$availableResource = $destinationColony->{'storage_'.$availableResource};
                }
                //crew => colony
                $destinationColony->military += $this->crew;
            }

            //vaisseaux -> colony
            foreach($this->ships as $ship)
            {
                $shipExists = $destinationColony->ships->filter(function ($value) use($ship){
                    return $value->id == $ship->id;
                });
                if($shipExists->count() > 0)
                {
                    $shipToUpdate = $shipExists->first();
                    $shipToUpdate->pivot->number += $ship->pivot->number;
                    $shipToUpdate->pivot->save();
                }
                else
                {
                    $destinationColony->ships()->attach([$ship->id => ['number' => $ship->pivot->number]]);
                }
            }

            $fleetMessage = trans('fleet.missionReturn', ['coordinateDestination' => $destCoordinates,
                                                'planetDest' => $destinationColony->name,
                                                'planetSource' => $sourceColony->name,
                                                'coordinateSource' => $sourceCoordinates,
                                                'fleet' => $this->shipsToString(),
                                                'resources' => $this->resourcesToString()
                                                ], $destinationColony->player->lang);

            $embed = [
                'author' => [
                    'name' => $destinationColony->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpScreen.jpg'],
                "title" => "Stargate",
                "description" => $fleetMessage,
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];

            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->embed = json_encode($embed);
            $reminder->player_id = $destinationColony->player->id;
            $reminder->save();

            //Une flotte composée de ... est arrivée sur ..., en porvenance de ... elle transporte ... (pas oublié le crew)

            $destinationColony->save();
            $this->ended = true;
            $this->save();
        }
        else
        {
            $now = Carbon::now();
            $departureDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->departure_date);
            $newArrivalDate = $departureDate->diffInSeconds($now);

            switch($this->mission)
            {
                case 'colonize':

                    $this->arrival_date = $now->addSeconds($newArrivalDate);
                    $this->returning = true;

                break;
                case 'transport':

                    $sourceCoordinates = $this->sourceColony->coordinates->humanCoordinates();
                    $destCoordinates = $this->destinationColony->coordinates->humanCoordinates();

                    $this->arrival_date = Carbon::now()->addSeconds($newArrivalDate);
                    $now = Carbon::now();
                    $fleetDuration = $now->diffForHumans($this->arrival_date,[
                        'parts' => 3,
                        'short' => true, // short syntax as per current locale
                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                    ]);

                    $transportMission = trans('fleet.transportMission', ['coordinateDestination' => $destCoordinates,
                                                                    'playerDest' => $this->destinationPlayer->user_name,
                                                                    'planetDest' => $this->destinationColony->name,
                                                                    'planetSource' => $this->sourceColony->name,
                                                                    'coordinateSource' => $sourceCoordinates,
                                                                    'fleet' => $this->shipsToString(),
                                                                    'resources' => $this->resourcesToString(),
                                                                    'duration' => $fleetDuration
                                                                    ], $this->destinationColony->player->lang);

                    if($this->player_source_id != $this->player_destination_id)
                    {
                        $fleetMessage = trans('fleet.transportReceived', ['coordinateDestination' => $destCoordinates,
                                                                        'playerSource' => $this->sourcePlayer->user_name,
                                                                        'planetDest' => $this->destinationColony->name,
                                                                        'planetSource' => $this->sourceColony->name,
                                                                        'coordinateSource' => $sourceCoordinates,
                                                                        'resources' => $this->resourcesToString()
                                                                        ], $this->destinationColony->player->lang);


                        try{
                            $tradeLogCheck = Trade::where([['player_id_dest',$this->destinationPlayer->id], ['player_id_source',$this->sourcePlayer->id], ['active', true]])
                                                    ->orWhere([['player_id_source',$this->destinationPlayer->id], ['player_id_dest',$this->sourcePlayer->id], ['active', true]])->first();

                            if(!is_null($tradeLogCheck))
                            {
                                $tradeLog = $tradeLogCheck;
                                $tradePlayer = '';
                                if($this->sourcePlayer->id == $tradeLog->player_id_dest)
                                    $tradePlayer = 1;
                                else
                                    $tradePlayer = 2;
                            }
                            else
                            {
                                $tradeLog = new Trade;
                                $tradeLog->player_id_source = $this->sourcePlayer->id;
                                $tradeLog->colony_source_id = $this->sourceColony->id;
                                $tradeLog->player_id_dest = $this->destinationPlayer->id;
                                $tradeLog->colony_destination_id = $this->destinationColony->id;
                                $tradeLog->trade_value_player1 = 0;
                                $tradeLog->trade_value_player2 = 0;
                                $tradeLog->save();
                                $tradePlayer = 1;
                            }
                        }
                        catch(\Exception $e)
                        {
                            echo $e->getMessage();
                        }
                    }

                    //Ressources -> colony
                    foreach($availableResources as $availableResource)
                    {
                        if($this->$availableResource > 0)
                        {
                            $this->destinationColony->$availableResource += $this->$availableResource;
                            if(isset($this->destinationColony->{'storage_'.$availableResource}) && $this->destinationColony->{'storage_'.$availableResource} < $this->destinationColony->$availableResource)
                                $this->destinationColony->$availableResource = $this->destinationColony->{'storage_'.$availableResource};

                            if($this->player_source_id != $this->player_destination_id)
                            {
                                $tradeResource = new TradeResource;
                                $tradeResource->player = $tradePlayer;
                                $tradeResource->trade_id = $tradeLog->id;
                                $tradeResource->quantity = $this->$availableResource;
                                $tradeResource->resource = $availableResource;
                                $tradeResource->setValue();
                                $tradeResource->save();
                            }
                            $this->$availableResource = 0;
                        }
                    }
                    if($this->player_source_id != $this->player_destination_id)
                    {
                        $tradeLog->load('tradeResources');
                        $tradeLog->setTradeValue();
                        $tradeLog->save();
                    }

                    $this->destinationColony->save();
                    $this->returning = true;
                    $this->save();

                    if($this->player_source_id != $this->player_destination_id)
                    {
                        $embed = [
                            'author' => [
                                'name' => $this->destinationColony->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpScreen.jpg'],
                            "title" => "Stargate",
                            "description" => $fleetMessage,
                            'fields' => [
                            ],
                            'footer' => array(
                                'text'  => 'Stargate',
                            ),
                        ];

                        $reminder = new Reminder;
                        $reminder->reminder_date = Carbon::now()->add('1s');
                        $reminder->embed = json_encode($embed);
                        $reminder->player_id = $this->destinationColony->player->id;
                        $reminder->save();
                    }


                    $embed = [
                        'author' => [
                            'name' => $this->sourceColony->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                        ],
                        //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/malpScreen.jpg'],
                        "title" => "Stargate",
                        "description" => $transportMission,
                        'fields' => [
                        ],
                        'footer' => array(
                            'text'  => 'Stargate',
                        ),
                    ];

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->add('1s');
                    $reminder->embed = json_encode($embed);
                    $reminder->player_id = $this->sourceColony->player->id;
                    $reminder->save();

                break;
                case 'attack':
                    $win = $this->resolveFight();

                    if($win)
                    {
                        $this->arrival_date = $now->addSeconds($newArrivalDate);
                        $this->returning = true;
                    }
                    else
                    {
                        $this->ended = true;
                    }
                break;
                case 'spy':
                    $spySuccess = true;

                    if($spySuccess)
                    {
                        $this->arrival_date = $now->addSeconds($newArrivalDate);
                        $this->returning = true;
                    }
                    else
                    {
                        $this->ended = true;
                    }
                break;
            }


        }
    }

    public function shipCount(){
        $shipCount = 0;
        foreach($this->ships as $ship)
        {
            $shipCount += $ship->pivot->number;
        }
        return $shipCount;
    }

    public function shipsToString(){
        $fleetString = '';
        foreach($this->ships as $ship)
        {
            $fleetString .= $ship->pivot->number.' '.$ship->name."\n";
        }
        return $fleetString;
    }

    public function resourcesToString($lang='fr'){
        $resourcesString = '';

        $availableResources = config('stargate.resources');
        $availableResources[] = 'E2PZ';
        $availableResources[] = 'military';
        foreach($availableResources as $availableResource)
        {
            if($this->$availableResource > 0)
            {
                $resourcesString .= config('stargate.emotes.'.strtolower($availableResource))." ".ucfirst($availableResource).': '.number_format($this->$availableResource)."\n";
            }
        }
        if(empty($resourcesString))
            $resourcesString .= trans('generic.empty', [], $lang)."\n";

        return $resourcesString;
    }
}
