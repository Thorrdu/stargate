<?php

namespace App;

use App\Utility\FuncUtility;
use App\Utility\PlayerUtility;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Fleet extends Model
{
    public function ships(){
        return $this->belongsToMany('App\Ship')->withPivot('number');
    }

    public function units(){
        return $this->belongsToMany('App\Unit')->withPivot('number');
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

    public function gateFight()
    {
        return $this->hasOne('App\GateFight');
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

        return $travelTime;
    }

    public function outcome()
    {
        $availableResources = config('stargate.resources');
        $availableResources[] = 'E2PZ';
        $availableResources[] = 'military';

        if($this->returning || $this->mission == 'base')
        {

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
                if(!is_null($this->crew) && $this->crew > 0)
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

            //units -> colony
            foreach($this->units as $unit)
            {
                $unitExists = $destinationColony->units->filter(function ($value) use($unit){
                    return $value->id == $unit->id;
                });
                if($unitExists->count() > 0)
                {
                    $unitToUpdate = $unitExists->first();
                    $unitToUpdate->pivot->number += $unit->pivot->number;
                    $unitToUpdate->pivot->save();
                }
                else
                {
                    $destinationColony->units()->attach([$unit->id => ['number' => $unit->pivot->number]]);
                }
            }

            if($this->mission == 'scavenge')
            {
                $scavString = '';
                foreach($this->units as $unit)
                    $scavString .= trans('craft.'.$unit->slug.'.name', [], $this->sourcePlayer->lang).': '.number_format($unit->pivot->number)."\n";

                $fleetMessage = trans('fleet.scavengerReturn', ['coordinateDestination' => $destCoordinates,
                                                    'planetSource' => $sourceColony->name,
                                                    'coordinateSource' => $sourceCoordinates,
                                                    'fleet' => $scavString,
                                                    'resources' => $this->resourcesToString($this->sourcePlayer->lang, false)
                ], $destinationColony->player->lang);
            }
            else
            {
                $fleetMessage = trans('fleet.missionReturn', ['coordinateDestination' => $destCoordinates,
                                                    'planetDest' => $destinationColony->name,
                                                    'planetSource' => $sourceColony->name,
                                                    'coordinateSource' => $sourceCoordinates,
                                                    'fleet' => $this->shipsToString(),
                                                    'resources' => $this->resourcesToString($this->sourcePlayer->lang)
                ], $destinationColony->player->lang);
            }

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
                case 'scavenge':
                    $this->arrival_date = $now->addSeconds($newArrivalDate);
                    $this->returning = true;

                    $totalResource = 0;
                    foreach(config('stargate.resources') as $resource)
                    {
                        $totalResource += $this->destinationColony->coordinates->$resource;
                    }

                    if($totalResource > 0)
                    {
                        $scavengedResString = '';
                        $totalCapacity = 0;
                        foreach($this->units as $fleetUnit)
                        {
                            $totalCapacity += $fleetUnit->capacity * $fleetUnit->pivot->number;
                        }

                        $claimAll = false;
                        if($totalCapacity >= $totalResource)
                            $claimAll = true;

                        foreach(config('stargate.resources') as $resource)
                        {
                            $ratio = $this->destinationColony->coordinates->$resource / $totalResource;

                            $claimed = 0;
                            if($claimAll)
                                $claimed = $this->destinationColony->coordinates->$resource;
                            else
                                $claimed = floor($totalCapacity*$ratio);

                            if($claimed > 0)
                            {
                                $scavengedResString .= config('stargate.emotes.'.strtolower($resource)).' '.ucfirst($resource).": ".number_format($claimed)."\n";
                                $this->destinationColony->coordinates->$resource -= $claimed;
                                $this->$resource = $claimed;
                            }
                        }
                        $this->destinationColony->coordinates->save();
                    }

                    $this->save();

                    if(empty($scavengedResString))
                        $scavengedResString = trans('fleet.emptyResources', [], $this->sourcePlayer->lang);

                    $scavengeMission = trans('fleet.scavengeMission', [
                        'coordinateDestination' => $this->destinationColony->coordinates->humanCoordinates(),
                        'planetSource' => $this->sourceColony->name,
                        'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
                        'resources' => $scavengedResString,
                    ], $this->destinationColony->player->lang);

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->add('1s');
                    $reminder->reminder = $scavengeMission;
                    $reminder->player_id = $this->sourcePlayer->id;
                    $reminder->save();

                break;
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
                                                                    'resources' => $this->resourcesToString($this->sourcePlayer->lang),
                                                                    'duration' => $fleetDuration
                                                                    ], $this->destinationColony->player->lang);

                    if($this->player_source_id != $this->player_destination_id)
                    {
                        $fleetMessage = trans('fleet.transportReceived', ['coordinateDestination' => $destCoordinates,
                                                                        'playerSource' => $this->sourcePlayer->user_name,
                                                                        'planetDest' => $this->destinationColony->name,
                                                                        'planetSource' => $this->sourceColony->name,
                                                                        'coordinateSource' => $sourceCoordinates,
                                                                        'resources' => $this->resourcesToString($this->destinationPlayer->lang)
                                                                        ], $this->destinationColony->player->lang);


                        try{
                            $tradeLogCheck = Trade::where([['player_id_dest',$this->destinationPlayer->id], ['player_id_source',$this->sourcePlayer->id], ['active', true]])
                                                    ->orWhere([['player_id_source',$this->destinationPlayer->id], ['player_id_dest',$this->sourcePlayer->id], ['active', true]])->first();

                            if(!is_null($tradeLogCheck))
                            {
                                $tradeLog = $tradeLogCheck;
                                $tradePlayer = '';
                                if($this->sourcePlayer->id == $tradeLog->player_id_source)
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
                            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
                    //units -> colony
                    foreach($this->units as $unit)
                    {
                        $unitExists = $this->destinationColony->units->filter(function ($value) use($unit){
                            return $value->id == $unit->id;
                        });
                        if($unitExists->count() > 0)
                        {
                            $unitToUpdate = $unitExists->first();
                            $unitToUpdate->pivot->number += $unit->pivot->number;
                            $unitToUpdate->pivot->save();
                        }
                        else
                        {
                            $this->destinationColony->ships()->attach([$unit->id => ['number' => $unit->pivot->number]]);
                        }
                        if($this->player_source_id != $this->player_destination_id)
                        {
                            $tradeResource = new TradeResource;
                            $tradeResource->player = $tradePlayer;
                            $tradeResource->trade_id = $tradeLog->id;
                            $tradeResource->quantity = $unit->pivot->number;
                            $tradeResource->unit_id = $unit->id;
                            $tradeResource->setValue();
                            $tradeResource->save();
                        }
                        $this->units()->detach($unit->id);
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
                    $winState = $this->resolveFight();
                    if($winState)
                    {
                        $this->arrival_date = $now->addSeconds($newArrivalDate);
                        $this->returning = true;
                        $this->save();
                    }
                    else
                    {
                        $this->ended = true;
                        $this->save();
                    }
                break;
                case 'spy':
                        PlayerUtility::spy($this->sourceColony, $this->destinationColony);
                        $this->ended = true;
                        $this->save();
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

    public function unitCount(){
        $unitCount = 0;
        foreach($this->units as $unit)
        {
            $unitCount += $unit->pivot->number;
        }
        return $unitCount;
    }

    public function shipsToString(){
        $fleetString = '';
        foreach($this->ships as $ship)
        {
            $fleetString .= $ship->pivot->number.' '.$ship->name."\n";
        }
        return $fleetString;
    }

    public function resourcesToString($lang='fr',$withUnit = true){
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
        if($withUnit)
        {
            foreach($this->units as $unit)
                $resourcesString .= trans('craft.'.$unit->slug.'.name', [], $lang).': '.number_format($unit->pivot->number)."\n";
        }

        if(empty($resourcesString))
            $resourcesString .= trans('generic.empty', [], $lang)."\n";

        return $resourcesString;
    }

    public function resolveFight()
    {
        //AutoWin
        if($this->destinationColony->defences->count() == 0 && $this->destinationColony->ships->count() == 0)
            return 'win';

        $attackerFireCoef = $defenderFireCoef = 1;
        $attackerShieldCoef = $defenderShieldCoef = 1;
        $attackerHullCoef = $defenderHullCoef = 1;

        $fleetTechnologies = Technology::Where('slug', 'LIKE', 'hull')->orWhere('slug', 'LIKE', 'shield')->orWhere('slug', 'LIKE', 'armament')->get();
        foreach($fleetTechnologies as $fleetTech)
        {
            $attackerTechLevel = $this->sourcePlayer->hasTechnology($fleetTech);
            if($attackerTechLevel)
            {
                switch($fleetTech->slug)
                {
                    case 'armament':
                        $attackerFireCoef *= pow(1.1,$attackerTechLevel);
                    break;
                    case 'shield':
                        $attackerShieldCoef *= pow(1.1,$attackerTechLevel);
                    break;
                    case 'hull':
                        $attackerHullCoef *= pow(1.1,$attackerTechLevel);
                    break;
                    default:
                    break;
                }
            }
            $defenderTechLevel = $this->sourcePlayer->hasTechnology($fleetTech);
            if($defenderTechLevel)
            {
                switch($fleetTech->slug)
                {
                    case 'armament':
                        $defenderFireCoef *= pow(1.1,$defenderTechLevel);
                    break;
                    case 'shield':
                        $defenderShieldCoef *= pow(1.1,$defenderTechLevel);
                    break;
                    case 'hull':
                        $defenderHullCoef *= pow(1.1,$defenderTechLevel);
                    break;
                    default:
                    break;
                }
            }
        }

        $defenceForces = array();
        foreach($this->destinationColony->ships as $ship)
        {
            $defenceForces[] = array(
                'type' => 'ship',
                'item' => $ship,
                'quantity' => $ship->pivot->number,
                'fire_power' => $ship->fire_power * $defenderFireCoef,
                'total_fire_power' => $ship->fire_power * $defenderFireCoef * $ship->pivot->number,
                'shield' => $ship->shield * $defenderShieldCoef,
                'hull' => $ship->hull * $defenderHullCoef,
                'shield_left' => $ship->shield * $defenderShieldCoef,
                'hull_left' => $ship->hull * $defenderHullCoef
            );
        }

        foreach($this->destinationColony->defences as $defence)
        {
            $defenceForces[] = array(
                'type' => 'defence',
                'item' => $defence,
                'quantity' => $defence->pivot->number,
                'fire_power' => $defence->fire_power * $defenderFireCoef,
                'total_fire_power' => $defence->fire_power * $defenderFireCoef * $defence->pivot->number,
                'shield' => 0,
                'hull' => $defence->hull * $defenderHullCoef,
                'shield_left' => 0,
                'hull_left' => $defence->hull * $defenderHullCoef
            );
        }

        $attackForces = array();
        foreach($this->ships as $ship)
        {
            $attackForces[] = array(
                'type' => 'ship',
                'item' => $ship,
                'quantity' => $ship->pivot->number,
                'fire_power' => $ship->fire_power * $attackerFireCoef,
                'total_fire_power' => $ship->fire_power * $attackerFireCoef * $ship->pivot->number,
                'shield' => $ship->shield * $attackerShieldCoef,
                'hull' => $ship->hull * $attackerHullCoef,
                'shield_left' => $ship->shield * $attackerShieldCoef,
                'hull_left' => $ship->hull * $attackerHullCoef
            );
        }


        //Le combat

        /**
         *
            Si la flotte attaquante possède plus de 3 fois l\'armement de celle du défenseur, et cela à n'importe quel moment du combat, (dès la première passe ou à la dixième passe si l\'armement du défenseur chûte durant le combat), la flotte du défenseur passe en mode défensif : chaque vaisseau se comportera comme une défense, et attaquera en premier les vaisseaux attaquants ayant le moins de puissance de feu.
            Les missiles d'interceptions (défense) attaqueront en premier les vaisseaux ayant le moins de défense (en ajoutant coque et bouclier), notamment les vaisseaux dits "riposteurs" ou les « cargos ».
        */

        /**
         *
            A chaque fin de passe les boucliers récupèrent un certain pourcentage de leur puissance en fonction de ce qu'ils ont perdu pendant cette passe.

            Ce pourcentage part de 100% (passe 1) et perd 10% par passe.
         */

        /**

            A l’issu du combat, les vaisseaux et 5% des défenses spatiales détruites se transforment en ruines représentant 75% des ressources en fer, or et cristal utilisés lors de leurs constructions (l’hydrogène est donc totalement perdu). Les CDR sont visibles dans les capteurs interstellaires et récupérables par l’ensemble des joueurs même ceux n’ayant pas participé au combat.
            Ces ruines peuvent être récupérées grâce à des vaisseaux ruines.

            Si vous apercevez un CDR en vous promenant dans les capteurs, il suffit de cliquer sur l’image le représentant. Vos VR les plus rapides de votre planète sur laquelle vous vous trouvez à ce moment partiront recycler ces ruines directement, le jeu calcule automatiquement le nombre de VR nécessaires pour recycler le CDR et n’enverra donc que le stricte nécessaire, si vous voulez en envoyer plus, il faut le faire manuellement. Un clic permet d’envoyer un modèle de VR, si vos VR les plus récents ne suffisent pas à tout recycler, il faut re-cliquer sur l’image et vos modèles plus anciens partiront, l’ordre d’envoi est le suivant : VR3, VR2, VR1, prototypes.

            Le Champ de Ruines ainsi Créer, se Matérialisera Instantanément en Orbite comme ceci :
            - 40% du CDR se trouvera au-dessus de la planète où le combat à eut lieu.
            - 10% sur la planète située immédiatement au-dessus dans les capteurs.
            - 20% sur la planète située immédiatement au-dessus de cette dernière.
            - 20% sur la planète située immédiatement en-dessous dans les capteurs de la planète d’origine.
            - 10% sur la planète située immédiatement en-dessous.


            Pour que les vaisseaux du défenseur puissent fonctionner à 100 %, il faut que leurs équipages (soldats) soient au complet. Les soldats doivent se trouver sur la même planète que les vaisseaux.
            S’il n’y a pas assez de soldats pour faire fonctionner à 100 % les vaisseaux, ces derniers participent tout de même au combat mais ne bénéficient alors pas des bonus offerts par les technologies débloquées par le joueur.
            Cela ne concerne que le défenseur car l’attaquant est obligé d’avoir son équipage au complet s’il veut lancer une attaque.

            =>Si pas assez de crew, ship détruit?
         */


        /**

        La flotte de l’attaquant va attaquer l’entité du défenseur (vaisseau ou défense) qui a l\'armement la plus grande donc la ligne 1 jusqu’à sa destruction complète puis il attaquer la ligne 2 puis 3...

        La flotte du défenseur va attaquer le type de vaisseau de l'attaquant qui a l\'armement la plus grande et donc à partir de la ligne 1 puis il attaquera la ligne 2 puis 3...


        PREVOIR LE CAS
        =>Si aucun gagnant au bout de la x ieme phase
        */

        $attackForces = FuncUtility::array_orderby($attackForces, 'fire_power', SORT_ASC, 'shield', SORT_ASC, 'hull', SORT_ASC);
        //ATTENTION, les vaisseaux de la défenses, eux, attaquent ce qui a le plus gros dps
        //trouver un moyen ?

        $defenceForces = FuncUtility::array_orderby($defenceForces, 'type', SORT_ASC, 'fire_power', SORT_DESC, 'shield', SORT_DESC, 'hull', SORT_DESC);

        $winState = false;

        $fleetReportFR = trans('fleet.battleSummary', [
            'playerSource' => $this->sourcePlayer->user_name,
            'playerDest' => $this->destinationPlayer->user_name,
            'colonySource' => $this->sourceColony->name,
            'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
            'colonyDest' => $this->destinationColony->name,
            'coordinateDest' => $this->destinationColony->coordinates->humanCoordinates(),
            'attackForces' => $this->summarizeForces($attackForces,'fr'),
            'defenceForces' => $this->summarizeForces($defenceForces,'fr')
        ], 'fr');

        $fleetReportEN = trans('fleet.battleSummary', [
            'playerSource' => $this->sourcePlayer->user_name,
            'playerDest' => $this->destinationPlayer->user_name,
            'colonySource' => $this->sourceColony->name,
            'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
            'colonyDest' => $this->destinationColony->name,
            'coordinateDest' => $this->destinationColony->coordinates->humanCoordinates(),
            'attackForces' => $this->summarizeForces($attackForces,'en'),
            'defenceForces' => $this->summarizeForces($defenceForces,'en')
        ], 'en');

        $globalLostAttackForces = $globalLostDefenceForces = 0;
        $defenceFleetDefenceMode = false;

        $ruinfield = ['iron' => 0, 'gold' => 0, 'quartz' => 0];

        for( $phase = 1 ; $phase <= 14 ; $phase++ )
        {
            if(empty($attackForces))
            {
                $winState = false;
                break;
            }
            if(empty($defenceForces))
            {
                $winState = true;
                break;
            }

            $fleetDamage = floor(array_sum(array_column($attackForces, 'total_fire_power')));
            $defenceDamage = floor(array_sum(array_column($defenceForces, 'total_fire_power')));

            if(!$defenceFleetDefenceMode && ($fleetDamage/3) > $defenceDamage)
            {
                $defenceFleetDefenceMode = true;
                $attackForces = FuncUtility::array_orderby($attackForces, 'fire_power', SORT_ASC, 'shield', SORT_ASC, 'hull', SORT_ASC);
                /**
                 * https://forum.origins-return.fr/index.php?/topic/241854-le-combat-spatial/
                Si à un moment du combat (de la 1ere à la 14ème passe), la flotte du défenseur (vaisseaux seulement) possède une attaque plus de 3 fois inférieur à celle de l’attaquant, alors elle passera en mode défensif et attaquera le vaisseau de l’attaquant avec le moins de pdf (en cas d’égalité entre plusieurs modèles, celui parmi ces derniers qui possède le moins de bouclier, puis celui qui a le moins de coque).
                Donc dans l'exemple ci-dessus : la ligne 6 puis 5 puis 4...

                Les défenses terrestres et spatiales (voir guide les défenses) attaqueront le vaisseau de l’attaquant avec le moins de pdf (en cas d’égalité entre plusieurs modèles, celui parmi ces derniers qui possède le moins de bouclier, puis celui qui avec le moins de coque).
                Donc en reprenant l'exemple : la ligne 6 puis 5 puis 4...

                Les missiles attaqueront le vaisseau de l’attaquant avec le moins de pouvoir défensif (addition bouclier + coque). Donc ici, la ligne 5 puis 6 puis 3 puis 2 puis 4 puis 1.
                  */
            }
            //Dégats du défenseur
            $attackShieldAbsorbed = 0;
            $defenceDamageLeft = $defenceDamage;
            $lostAttackForces = [];
            foreach($attackForces as $key => $forceUnit)
            {
                //Regen shield
                if($forceUnit['shield_left'] < $forceUnit['shield'] && $phase > 1 && $phase < 12)
                    $forceUnit['shield_left'] += (($forceUnit['shield']-$forceUnit['shield_left']) * 0.1) * (12-$phase);
                //Au moins 1 vaisseau détruit
                if($defenceDamageLeft >= ($forceUnit['shield_left'] + $forceUnit['hull_left']))
                {
                    /**
                     *
                     * Est-ce que plusieurs vaisseaux détruits ?
                     *
                     */
                    $defenceDamageLeft -= ($forceUnit['shield_left'] + $forceUnit['hull_left']);
                    $attackShieldAbsorbed += $forceUnit['shield_left'];
                    $nbrShipDestroyed = 1+floor($defenceDamageLeft/($forceUnit['shield'] + $forceUnit['hull']));
                    if($nbrShipDestroyed > 1 && $forceUnit['quantity'] > 1)
                    {
                        if($nbrShipDestroyed > $forceUnit['quantity'])
                            $nbrShipDestroyed = $forceUnit['quantity'];

                        $attackForces[$key]['quantity'] -= $nbrShipDestroyed;
                        $globalLostAttackForces += $nbrShipDestroyed;
                        $defenceDamageLeft -= ($forceUnit['shield'] + $forceUnit['hull']) * ($nbrShipDestroyed-1);

                        $lostAttackForces[] = $forceUnit;
                        $lostAttackForces[count($lostAttackForces)-1]['quantity'] = $nbrShipDestroyed;

                        $attackShieldAbsorbed += $forceUnit['shield'] * ($nbrShipDestroyed-1);

                        //Si au moins un autre vaisseau survit, on remet hull et shield max
                        $attackForces[$key]['shield_left'] = $forceUnit['shield'];
                        $attackForces[$key]['hull_left'] = $forceUnit['hull'];
                    }
                    else
                    {
                        $nbrShipDestroyed = 1;
                        $attackForces[$key]['quantity'] -= 1;
                        $globalLostAttackForces ++;
                        $lostAttackForces[] = $forceUnit;
                        $lostAttackForces[count($lostAttackForces)-1]['quantity'] = 1;
                        $attackForces[$key]['shield_left'] = $forceUnit['shield'];
                        $attackForces[$key]['hull_left'] = $forceUnit['hull'];
                    }
                    $attackForces[$key]['total_fire_power'] = $attackForces[$key]['quantity'] * $attackForces[$key]['fire_power'];

                    $shipPrice = $forceUnit['item']->getPrice($nbrShipDestroyed);
                    foreach(config('stargate.resources') as $resource)
                    {
                        if($resource != 'naqahdah')
                            $ruinfield[$resource] += floor($shipPrice[$resource]*0.75);
                    }

                    if($attackForces[$key]['quantity'] <= 0)
                    {
                        //Tous les vaisseaux de la ligne détruits
                        $this->ships()->detach($forceUnit['item']->id);
                        unset($attackForces[$key]);
                    }
                    else
                    {
                        /**
                         *
                         * Est-ce que vaisseau endomagé avec ce qui reste?
                         *
                         */
                        if($defenceDamageLeft >= $forceUnit['shield_left'])
                        {
                            $defenceDamageLeft -= $forceUnit['shield_left'];
                            $attackShieldAbsorbed += $forceUnit['shield_left'];
                            $attackForces[$key]['shield_left'] = 0;
                            $attackForces[$key]['hull_left'] -= $defenceDamageLeft;
                        }
                        else
                        {
                            $attackShieldAbsorbed += $defenceDamageLeft;
                            $attackForces[$key]['shield_left'] -= $defenceDamageLeft;
                        }
                        $defenceDamageLeft = 0;
                    }
                }
                else
                {
                    if($defenceDamageLeft >= $forceUnit['shield_left'])
                    {
                        $attackShieldAbsorbed += $forceUnit['shield_left'];
                        $defenceDamageLeft -= $forceUnit['shield_left'];
                        $attackForces[$key]['shield_left'] = 0;
                        $attackForces[$key]['hull_left'] -= $defenceDamageLeft;
                    }
                    else
                    {
                        $attackShieldAbsorbed += $defenceDamageLeft;
                        $attackForces[$key]['shield_left'] -= $defenceDamageLeft;
                    }
                    $defenceDamageLeft = 0;
                }
                array_values(array_filter($attackForces));
                if($defenceDamageLeft == 0)
                    break;
            }


            //Dégats de l'attaquant
            $defenceShieldAbsorbed = 0;
            $fleetDamageLeft = $fleetDamage;
            $lostDefenceForces = [];
            foreach($defenceForces as $key => $forceUnit)
            {
                //Regen shield
                if($forceUnit['shield_left'] < $forceUnit['shield'] && $phase > 1 && $phase < 12)
                    $forceUnit['shield_left'] += (($forceUnit['shield']-$forceUnit['shield_left']) * 0.1) * (12-$phase);

                //Au moins 1 vaisseau détruit
                if($fleetDamageLeft >= ($forceUnit['shield_left'] + $forceUnit['hull_left']))
                {
                    /**
                     *
                     * Est-ce que plusieurs vaisseaux détruits ?
                     *
                     */
                    $fleetDamageLeft -= ($forceUnit['shield_left'] + $forceUnit['hull_left']);
                    $defenceShieldAbsorbed += $forceUnit['shield_left'];
                    $nbrShipDestroyed = 1+floor($fleetDamageLeft/($forceUnit['shield'] + $forceUnit['hull']));
                    if($nbrShipDestroyed > 1 && $forceUnit['quantity'] > 1)
                    {
                        if($nbrShipDestroyed > $forceUnit['quantity'])
                            $nbrShipDestroyed = $forceUnit['quantity'];

                        $defenceForces[$key]['quantity'] -= $nbrShipDestroyed;
                        $globalLostDefenceForces += $nbrShipDestroyed;
                        $fleetDamageLeft -= ($forceUnit['shield'] + $forceUnit['hull']) * ($nbrShipDestroyed-1);

                        $lostDefenceForces[] = $forceUnit;
                        $lostDefenceForces[count($lostDefenceForces)-1]['quantity'] = $nbrShipDestroyed;

                        $defenceShieldAbsorbed += $forceUnit['shield'] * ($nbrShipDestroyed-1);

                        //Si au moins un autre vaisseau survit, on remet hull et shield max
                        $defenceForces[$key]['shield_left'] = $forceUnit['shield'];
                        $defenceForces[$key]['hull_left'] = $forceUnit['hull'];
                    }
                    else
                    {
                        $nbrShipDestroyed = 1;
                        $defenceForces[$key]['quantity'] -= 1;
                        $globalLostDefenceForces ++;
                        $lostDefenceForces[] = $forceUnit;
                        $lostDefenceForces[count($lostDefenceForces)-1]['quantity'] = 1;
                        $defenceForces[$key]['shield_left'] = $forceUnit['shield'];
                        $defenceForces[$key]['hull_left'] = $forceUnit['hull'];
                    }
                    $defenceForces[$key]['total_fire_power'] = $defenceForces[$key]['quantity'] * $defenceForces[$key]['fire_power'];

                    if($forceUnit['type'] == 'ship')
                    {
                        $shipPrice = $forceUnit['item']->getPrice($nbrShipDestroyed);
                        foreach(config('stargate.resources') as $resource)
                        {
                            if($resource != 'naqahdah')
                                $ruinfield[$resource] += floor($shipPrice[$resource]*0.75);
                        }
                    }

                    if($defenceForces[$key]['quantity'] <= 0)
                    {
                        if($forceUnit['type'] == 'ship')
                        {
                            $this->destinationColony->military -= $nbrShipDestroyed * $forceUnit['item']->crew;
                            $this->destinationColony->ships()->detach($forceUnit['item']->id);
                        }
                        elseif($defenceForces[$key]['quantity'] == 1)
                        {
                            $this->destinationColony->defences()->detach($forceUnit['item']->id);
                        }
                        else
                        {
                            $defenceForces[$key]['item']->pivot->number = floor($defenceForces[$key]['item']->pivot->number * 0.95);
                            $defenceForces[$key]['item']->pivot->save();
                        }
                        //Tous les vaisseaux de la ligne détruits
                        unset($defenceForces[$key]);

                    }
                    else
                    {
                        /**
                         *
                         * Est-ce que vaisseau endomagé avec ce qui reste?
                         *
                         */
                        if($fleetDamageLeft >= $forceUnit['shield_left'])
                        {
                            $fleetDamageLeft -= $forceUnit['shield_left'];
                            $defenceShieldAbsorbed += $forceUnit['shield_left'];
                            $defenceForces[$key]['shield_left'] = 0;
                            $defenceForces[$key]['hull_left'] -= $fleetDamageLeft;
                        }
                        else
                        {
                            $defenceShieldAbsorbed += $fleetDamageLeft;
                            $defenceForces[$key]['shield_left'] -= $fleetDamageLeft;
                        }
                        $fleetDamageLeft = 0;
                    }
                }
                else
                {
                    if($fleetDamageLeft >= $forceUnit['shield_left'])
                    {
                        $defenceShieldAbsorbed += $forceUnit['shield_left'];
                        $fleetDamageLeft -= $forceUnit['shield_left'];
                        $defenceForces[$key]['shield_left'] = 0;
                        $defenceForces[$key]['hull_left'] -= $fleetDamageLeft;
                    }
                    else
                    {
                        $defenceShieldAbsorbed += $fleetDamageLeft;;
                        $defenceForces[$key]['shield_left'] -= $fleetDamageLeft;
                    }
                    $fleetDamageLeft = 0;
                }
                array_values(array_filter($defenceForces));
                if($fleetDamageLeft == 0)
                    break;
            }

            $fleetReportFR .= trans('fleet.passSummary', [
                'phaseNbr' => $phase,
                'attackerDamageDone' => number_format($fleetDamage),
                'defenderAbsorbedDamage' => number_format($defenceShieldAbsorbed),
                'defenderDamageDone' => number_format($defenceDamage),
                'attackerAbsorbedDamage' => number_format($attackShieldAbsorbed),
                'lostAttackerUnits' => $this->summarizeForces($lostAttackForces, 'fr', false),
                'lostDefenderUnits' => $this->summarizeForces($lostDefenceForces, 'fr', false)
            ], 'fr');

            $fleetReportEN .= trans('fleet.passSummary', [
                'phaseNbr' => $phase,
                'attackerDamageDone' => number_format($fleetDamage),
                'defenderAbsorbedDamage' => number_format($defenceShieldAbsorbed),
                'defenderDamageDone' => number_format($defenceDamage),
                'attackerAbsorbedDamage' => number_format($attackShieldAbsorbed),
                'lostAttackerUnits' => $this->summarizeForces($lostAttackForces, 'en', false),
                'lostDefenderUnits' => $this->summarizeForces($lostDefenceForces, 'en', false)
            ], 'en');
        }

        //final update fleet ships
        $newCapacity = 0;
        $newCrew = 0;
        if(!empty($attackForces))
        {
            foreach($attackForces as $forceUnit)
            {
                if($forceUnit['quantity'] != $forceUnit['item']->pivot->number)
                {
                    $forceUnit['item']->pivot->number = $forceUnit['quantity'];
                    $forceUnit['item']->pivot->save();
                }
                $newCapacity += $forceUnit['item']->pivot->number * $forceUnit['item']->capacity;
                $newCrew += $forceUnit['item']->pivot->number * $forceUnit['item']->crew;
            }
        }
        $this->capacity = $newCapacity;
        $this->crew = $newCrew;

        //final update Defence fleet ships
        if(!empty($defenceForces))
        {
            foreach($defenceForces as $forceUnit)
            {
                if($forceUnit['quantity'] != $forceUnit['item']->pivot->number)
                {
                    if($forceUnit['type'] == 'ship')
                        $forceUnit['item']->pivot->number = $forceUnit['quantity'];
                    else
                        $forceUnit['item']->pivot->number -= ceil(($forceUnit['item']->pivot->number-$forceUnit['quantity'])*0.05);
                    $forceUnit['item']->pivot->save();
                }
            }
        }

        $ruinFieldString = '';
        if($ruinfield['iron'] > 0)
        {
            foreach(config('stargate.resources') as $resource)
            {
                if($resource != 'naqahdah')
                {
                    $ruinFieldString .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format($ruinfield[$resource]);
                    $this->destinationColony->coordinates->$resource += $ruinfield[$resource];
                }
            }
            $ruinFieldStringFR = "\n".trans('fleet.ruinFieldGenerated', ['resources' => $ruinFieldString], 'fr');
            $ruinFieldStringEN = "\n".trans('fleet.ruinFieldGenerated', ['resources' => $ruinFieldString], 'en');
            $this->destinationColony->coordinates->save();
        }

        $attackLog = GateFight::where('fleet_id',$this->id)->get()->first();
        if($winState)
        {
            //Pillage avec forces restantes
            //recalculer capacité
            $totalResource = 0;
            $stolenResources = '';
            foreach(config('stargate.resources') as $resource)
            {
                $totalResource += $this->destinationColony->$resource;
            }
            $claimAll = false;
            if($this->capacity >= ($totalResource*0.6))
                $claimAll = true;

            foreach(config('stargate.resources') as $resource)
            {
                if($this->destinationColony->$resource > 1)
                {
                    $ratio = $this->destinationColony->$resource / $totalResource;
                    $maxClaimable = ceil($this->destinationColony->$resource * 0.6);

                    $claimed = 0;
                    if($claimAll)
                        $claimed = $maxClaimable;
                    else
                        $claimed = floor($this->capacity*$ratio);

                    if($claimed > 0)
                    {
                        $stolenResources .= config('stargate.emotes.'.strtolower($resource)).' '.ucfirst($resource).": ".number_format($claimed)."\n";
                        $this->$resource = $claimed;
                        $attackLog->$resource = $claimed;
                        $this->destinationColony->$resource -= $claimed;
                    }
                }
            }

            $battleResultFR = trans('fleet.battleWin', [
                'lostAttackUnit' => number_format($globalLostAttackForces),
                'lostDefenceUnit' => number_format($globalLostDefenceForces),
                'stolenResources' => $stolenResources
            ], 'fr').$ruinFieldStringFR;

            $battleResultEN = trans('fleet.battleWin', [
                'lostAttackUnit' => number_format($globalLostAttackForces),
                'lostDefenceUnit' => number_format($globalLostDefenceForces),
                'stolenResources' => $stolenResources
            ], 'en').$ruinFieldStringEN;

            $attackLog->player_id_winner = $this->sourcePlayer->id;
            $attackLog->report_fr = $fleetReportFR.$battleResultFR;
            $attackLog->report_en = $fleetReportEN.$battleResultEN;
            $attackLog->save();

            $this->destinationColony->save();
            $this->save();

            $winReport = trans('fleet.attackArrived', [
                'playerDest' => $this->destinationPlayer->user_name,
                'planetSource' => $this->sourceColony->name,
                'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
                'planetDest' => $this->destinationColony->name,
                'coordinateDestination' => $this->destinationColony->coordinates->humanCoordinates(),
                'battleResult' => ${'battleResult'.strtoupper($this->sourcePlayer->lang)},
                'fleetId' => $this->id
            ], $this->sourcePlayer->lang);

            $embed = [
                'author' => [
                    'name' => $this->sourcePlayer->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/planetAttack.gif'],
                "title" => "Stargate",
                "description" => $winReport,
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];
            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->embed = json_encode($embed);
            $reminder->player_id = $this->sourcePlayer->id;
            $reminder->save();

            $defenceLostReport = trans('fleet.attacked', [
                'playerSource' => $this->sourcePlayer->user_name,
                'planetSource' => $this->sourceColony->name,
                'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
                'planetDest' => $this->destinationColony->name,
                'coordinateDestination' => $this->destinationColony->coordinates->humanCoordinates(),
                'battleResult' => ${'battleResult'.strtoupper($this->destinationPlayer->lang)},
                'fleetId' => $this->id
            ], $this->destinationPlayer->lang);
            $embed = [
                'author' => [
                    'name' => $this->destinationPlayer->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/planetAttack.gif'],
                "title" => "Stargate",
                "description" => $defenceLostReport,
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];
            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->embed = json_encode($embed);
            $reminder->player_id = $this->destinationPlayer->id;
            $reminder->save();
        }
        else
        {
            //Perdu...
            $battleResultFR = trans('fleet.battleLost', [
                'lostAttackUnit' => number_format($globalLostAttackForces),
                'lostDefenceUnit' => number_format($globalLostDefenceForces)
            ], 'fr').$ruinFieldStringEN;

            $battleResultEN = trans('fleet.battleLost', [
                'lostAttackUnit' => number_format($globalLostAttackForces),
                'lostDefenceUnit' => number_format($globalLostDefenceForces)
            ], 'en').$ruinFieldStringEN;

            $attackLog->player_id_winner = $this->destinationPlayer->id;
            $attackLog->report_fr = $fleetReportFR.$battleResultFR;
            $attackLog->report_en = $fleetReportEN.$battleResultEN;
            $attackLog->save();

            $this->destinationColony->save();
            $this->save();

            $fleetLostReport = trans('fleet.attackArrived', [
                'playerDest' => $this->destinationPlayer->user_name,
                'planetSource' => $this->sourceColony->name,
                'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
                'planetDest' => $this->destinationColony->name,
                'coordinateDestination' => $this->destinationColony->coordinates->humanCoordinates(),
                'battleResult' => ${'battleResult'.strtoupper($this->sourcePlayer->lang)},
                'fleetId' => $this->id
            ], $this->sourcePlayer->lang);

            $embed = [
                'author' => [
                    'name' => $this->sourcePlayer->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/shiplost.png'],
                "title" => "Stargate",
                "description" => $fleetLostReport,
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];
            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->embed = json_encode($embed);
            $reminder->player_id = $this->sourcePlayer->id;
            $reminder->save();

            $defenceSuccessReport = trans('fleet.attacked', [
                'playerSource' => $this->sourcePlayer->user_name,
                'planetSource' => $this->sourceColony->name,
                'coordinateSource' => $this->sourceColony->coordinates->humanCoordinates(),
                'planetDest' => $this->destinationColony->name,
                'coordinateDestination' => $this->destinationColony->coordinates->humanCoordinates(),
                'battleResult' => ${'battleResult'.strtoupper($this->destinationPlayer->lang)},
                'fleetId' => $this->id
            ], $this->destinationPlayer->lang);
            $embed = [
                'author' => [
                    'name' => $this->destinationPlayer->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/spacedebris.png'],
                "title" => "Stargate",
                "description" => $defenceSuccessReport,
                'fields' => [
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];
            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->embed = json_encode($embed);
            $reminder->player_id = $this->destinationPlayer->id;
            $reminder->save();
        }

        return $winState;
    }

    public function summarizeForces($forces, $lang='fr', $withAttributes = true)
    {
        if(empty($forces) && $withAttributes)
            return trans('stargate.emptyFleet', [], $lang)."\n";
        elseif(empty($forces) && !$withAttributes)
            return "/\n";

        $returnString = '';
        $totalShield = 0;
        $totalHull = 0;
        foreach($forces as $forceUnit)
        {
            $totalHull += $forceUnit['quantity'] * $forceUnit['hull'];
            $totalShield += $forceUnit['quantity'] * $forceUnit['shield'];

            if($forceUnit['type'] == 'defence')
                $returnString .= $forceUnit['quantity'].' x '.trans('defence.'.$forceUnit['item']->slug.'.name', [], $lang);
            else
                $returnString .= $forceUnit['quantity'].' x '.$forceUnit['item']->name;

            if($withAttributes)
                $returnString .= ' ( '.config('stargate.emotes.armament').' '.number_format($forceUnit['fire_power']).', '.config('stargate.emotes.shield').' '.number_format($forceUnit['shield']).', '.config('stargate.emotes.hull').' '.number_format($forceUnit['hull'])." )\n";
            else
                $returnString .= "\n";
        }
        if($withAttributes)
            $returnString .= 'Total: '.config('stargate.emotes.armament').' '.number_format(array_sum(array_column($forces, 'total_fire_power'))).', '.config('stargate.emotes.shield').' '.number_format($totalShield).', '.config('stargate.emotes.hull').' '.number_format($totalHull)."\n";


        return $returnString;
    }
}
