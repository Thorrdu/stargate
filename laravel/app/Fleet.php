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
                    $winState = $this->resolveFight();

                    if($winState)
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
                'shield' => $ship->shield * $attackerShieldCoef,
                'hull' => $ship->hull * $attackerHullCoef,
                'shield_left' => $ship->shield * $attackerShieldCoef,
                'hull_left' => $ship->hull * $attackerHullCoef
            );
        }

        $defenceLostForces = array();
        $attackLostForces = array();

        //Le combat

        /**
         *
            Si la flotte attaquante possède plus de 3 fois la puissance de feu de celle du défenseur, et cela à n'importe quel moment du combat, (dès la première passe ou à la dixième passe si la puissance de feu du défenseur chûte durant le combat), la flotte du défenseur passe en mode défensif : chaque vaisseau se comportera comme une défense, et attaquera en premier les vaisseaux attaquants ayant le moins de puissance de feu.
            Les missiles d'interceptions (défense) attaqueront en premier les vaisseaux ayant le moins de défense (en ajoutant coque et bouclier), notamment les vaisseaux dits "riposteurs" ou les « cargos ».
        */

        /**
         *
            A chaque fin de passe les boucliers récupèrent un certain pourcentage de leur puissance en fonction de ce qu'ils ont perdu pendant cette passe.

            Ce pourcentage part de 100% (passe 1) et perd 10% par passe.
         */

        /**
            14 passes max
            Pille 60%
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

        //Ecrire recap firepower/shield/hull avec totaux

        /**
         *
         array_orderby

         $sorting_insructions = [
            ['column'=>'first_name', 'order'=>'asc'],
            ['column'=>'date_of_birth', 'order'=>'desc'],
        ];
        for ($i = count($sorting_insructions) - 1; $i >= 0 ; $i--) {

            extract($sorting_insructions[i]);

            if ( $order === 'asc') {
                $collection = $collection->sortBy( $column );
            } else {
                $collection = $collection->sortByDesc( $column );
            }

        }


         $array = collect($array)->sortBy('count')->reverse()->toArray();



        $attackForces = array_values(Arr::sort($attackForces, function ($value) {
            return (0-$value['fire_power']);
        }));
         */

        /**

        La flotte de l’attaquant va attaquer l’entité du défenseur (vaisseau ou défense) qui a la puissance de feu la plus grande donc la ligne 1 jusqu’à sa destruction complète puis il attaquer la ligne 2 puis 3...

        La flotte du défenseur va attaquer le type de vaisseau de l'attaquant qui a la puissance de feu la plus grande et donc à partir de la ligne 1 puis il attaquera la ligne 2 puis 3...

        */

        $attackForces = FuncUtility::array_orderby($attackForces, 'fire_power', SORT_ASC, 'shield', SORT_ASC, 'hull', SORT_ASC);
        //ATTENTION, les vaisseaux de la défenses, eux, attaquent ce qui a le plus gros dps
        //trouver un moyen ?

        $defenceForces = FuncUtility::array_orderby($defenceForces, 'type', SORT_ASC, 'fire_power', SORT_DESC, 'shield', SORT_DESC, 'hull', SORT_DESC);
        $lostAttackForces[] = array();

        for( $phase = 1 ; $phase <= 10 ; $phase++ )
        {
            if(empty($attackForces))
                return 'loose';
            if(empty($defenceForces))
                return 'win';

            $fleetDamage = array_sum(array_column($attackForces, 'fire_power'));
            $defenceDamage = array_sum(array_column($defenceForces, 'fire_power'));

            if(($fleetDamage/3) > $defenceDamage)
            {
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
            $AttackShieldAbsorbed = 0;
            $defenceDamageLeft = $defenceDamage;
            for($shipCount = 0; $shipCount<count($attackForces); $shipCount++)
            {
                //Au moins 1 vaisseau détruit
                if($defenceDamage >= ($attackForces[$shipCount]['shield_left'] + $attackForces[$shipCount]['hull_left']))
                {
                    /**
                     *
                     * Est-ce que plusieurs vaisseaux détruits ?
                     *
                     */
                    $nbrShipDestroyed = floor($defenceDamageLeft/($attackForces[$shipCount]['shield'] + $attackForces[$shipCount]['hull']));
                    if($nbrShipDestroyed > 0)
                    {
                        $defenceDamageLeft -= ($attackForces[$shipCount]['shield'] + $attackForces[$shipCount]['hull']) * $nbrShipDestroyed;

                        //Si ship déjà présent => incrémenter
                        //si pas présent
                        $lostIndex = array_search($attackForces[$shipCount]['item'],$lostAttackForces);
                        if($lostIndex) //Si ship déjà présent => incrémenter
                            $lostAttackForces[$lostIndex]['quantity'] += $nbrShipDestroyed+1;
                        else{
                            $lostAttackForces[] = $attackForces[$shipCount];
                            $lostAttackForces[count($lostAttackForces)-1]['quantity'] = $nbrShipDestroyed+1;
                        }

                        //Si au moins un autre vaisseau survit, on remet hull et shield max
                        $attackForces[$shipCount]['shield_left'] = $attackForces[$shipCount]['shield'];
                        $attackForces[$shipCount]['hull_left'] = $attackForces[$shipCount]['hull'];
                    }

                    if($attackForces[$shipCount]['quantity'] <= $nbrShipDestroyed)
                    {
                        //Tous les vaisseaux de la ligne détruits
                        unset($attackForces[$shipCount]);
                        array_values($attackForces[$shipCount]);
                    }
                    else
                    {

                        /**
                         *
                         * Est-ce que vaisseau endomagé avec ce qui reste?
                         *
                         */
                        if($defenceDamageLeft >= $attackForces[$shipCount]['shield_left'])
                        {
                            $defenceDamageLeft -= $attackForces[$shipCount]['shield_left'];
                            $AttackShieldAbsorbed += $attackForces[$shipCount]['shield_left'];
                            $attackForces[$shipCount]['shield_left'] = 0;
                            $attackForces[$shipCount]['hull_left'] -= $defenceDamageLeft;
                        }
                        else
                        {
                            $AttackShieldAbsorbed += $defenceDamageLeft;
                            $attackForces[$shipCount]['shield_left'] -= $defenceDamageLeft;
                        }
                        $defenceDamageLeft = 0;
                    }
                }
                else
                {
                    if($defenceDamageLeft >= $attackForces[$shipCount]['shield_left'])
                    {
                        $AttackShieldAbsorbed += $attackForces[$shipCount]['shield_left'];
                        $defenceDamageLeft -= $attackForces[$shipCount]['shield_left'];
                        $attackForces[$shipCount]['shield_left'] = 0;
                        $attackForces[$shipCount]['hull_left'] -= $defenceDamageLeft;
                    }
                    else
                    {
                        $AttackShieldAbsorbed += $defenceDamageLeft;;
                        $attackForces[$shipCount]['shield_left'] -= $defenceDamageLeft;
                    }
                    $defenceDamageLeft = 0;
                }

                if($defenceDamageLeft == 0)
                    break;
            }


        }
    }
}
