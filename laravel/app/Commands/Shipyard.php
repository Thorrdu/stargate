<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\myDiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Ship;
use App\Building;
use App\ShipPart;
use App\Technology;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Shipyard extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $shipList;
    public $componentList;
    public $componentSelectedType;
    public $shipQueue;
    public $closed;
    public $queueType;
    public $blueprintMaker;

    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            $this->player->activeColony->checkColony();
            $this->player->refresh();

            try{
                echo PHP_EOL.'Execute Shipyard';

                $shipyard = Building::find(9); // Shipyard
                $currentShipyardLvl = $this->player->activeColony->hasBuilding($shipyard);
                if(!$currentShipyardLvl)
                {
                    return trans('shipyard.notBuilt', [], $this->player->lang);
                }

                if(empty($this->args) || Str::startsWith('list', $this->args[0]))
                {
                    $this->shipList = $this->player->ships;

                    if(count($this->player->ships) == 0)
                    {
                        return trans('shipyard.emptyList', [], $this->player->lang);
                    }

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->shipList->count()/5);
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
                        $this->paginatorMessage->createReactionCollector($filter);
                    });
                }
                elseif(Str::startsWith('rename', $this->args[0]) && count($this->args) >= 2)
                {
                    $ship = Ship::where([['player_id', $this->player->id],['slug', 'LIKE', $this->args[1].'%']])->first();
                    if(!is_null($ship))
                    {
                        $newShipName = trim(join(' ', array_slice($this->args, 2)));

                        if(strlen($newShipName) < 3)
                            return trans('generic.nameTooShort',[],$this->player->lang);

                        if(strlen($newShipName) > 35)
                            return trans('generic.nameTooLong',[],$this->player->lang);

                        $ship->name = $newShipName;
                        $ship->slug = Str::of($newShipName)->slug();
                        if(strlen($ship->slug) > 3)
                        {
                            $ship->save();
                            return trans('shipyard.shipNameChanged' , ['name' => $ship->name, 'slug' => $ship->slug], $this->player->lang);
                        }
                        else
                            return trans('generic.nameCantBeSlugged',[],$this->player->lang);
                    }
                    else
                        return trans('shipyard.unknownShip', [], $this->player->lang);
                }
                elseif(Str::startsWith('remove', $this->args[0]))
                {
                    //remove si n'en possède aucun en vol ou sur une colonie
                    $ship = Ship::where([['player_id', $this->player->id],['slug', 'LIKE', $this->args[1].'%']])->first();
                    if(!is_null($ship))
                    {
                        $shipOnColonies = DB::table('colony_ship')
                        ->where('ship_id', $ship->id)
                        ->count();

                        $shipInFleet = DB::table('fleet_ship')
                        ->join('fleets', 'fleets.id', '=', 'fleet_ship.fleet_id')
                        ->where([['fleet_ship.ship_id', $ship->id],['fleets.ended', false]])
                        ->count();

                        if($shipOnColonies > 0 || $shipInFleet > 0)
                            return trans('shipyard.impossibleRemoval', [], $this->player->lang);
                        else
                        {
                            $ship->player_id = null;
                            $ship->save();
                            return trans('shipyard.modelRemoved', [], $this->player->lang);
                        }
                    }
                    else
                        return trans('shipyard.unknownShip', [], $this->player->lang);
                }
                elseif(Str::startsWith('create', $this->args[0]))
                {
                    //Permet de créer un modèle de vaisseau sur base des composants sélectionnés
                    //Limite de 15 modèles par personne
                    try{

                        if(count($this->args) == 1)
                        {
                            $tutorialEmbed = [
                                'author' => [
                                    'name' => $this->player->user_name,
                                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                ],
                                "title" => trans('shipyard.customModelTitle', [], $this->player->lang),
                                "description" => trans('shipyard.customModelsTutorial', [], $this->player->lang),
                                'fields' => [],
                                'footer' => array(
                                    'text'  => 'Stargate',
                                ),
                            ];
                            $this->message->channel->sendMessage('', false, $tutorialEmbed);
                            return;
                        }

                        if($this->player->ships->count() >= 15)
                            return trans('shipyard.modelsLimitReached', [], $this->player->lang);

                        if(count($this->args) < 7)
                            return trans('generic.wrongParameter', [], $this->player->lang);

                        $this->blueprintMaker = [
                            'finalShip' => new Ship,
                            'blueprint' => null,
                            'armament' => [],
                            'shield' => [],
                            'hull' => [],
                            'reactor' => []
                        ];

                        $blueprint = ShipPart::Where([['slug', 'LIKE', $this->args[1].'%'],['type','Blueprint']])->first();
                        if(is_null($blueprint))
                            return trans('shipyard.unknownBlueprint', ['part' => $this->args[1]], $this->player->lang);
                        else
                        {
                            $hasRequirements = true;
                            foreach($blueprint->requiredTechnologies as $requiredTechnology)
                            {
                                if($requiredTechnology->id == 6)
                                    $this->blueprintMaker['finalShip']->required_blueprint = $requiredTechnology->pivot->level;

                                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                    $hasRequirements = false;
                            }
                            foreach($blueprint->requiredBuildings as $requiredBuilding)
                            {
                                if($requiredBuilding->id == 9)
                                    $this->blueprintMaker['finalShip']->required_shipyard = $requiredBuilding->pivot->level;

                                $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                    $hasRequirements = false;
                            }

                            if(!$hasRequirements)
                                return trans('generic.missingRequirements', [], $this->player->lang);

                            $this->blueprintMaker['blueprint'] = $blueprint;
                            $this->blueprintMaker['finalShip']->capacity = $blueprint->capacity;
                            $this->blueprintMaker['finalShip']->crew = $blueprint->crew;
                            $this->blueprintMaker['finalShip']->base_time = $blueprint->base_time;
                            $this->blueprintMaker['finalShip']->fire_power = $this->blueprintMaker['finalShip']->shield = $this->blueprintMaker['finalShip']->hull = $this->blueprintMaker['finalShip']->speed = 0;

                            $partPrice = $blueprint->getPrice();
                            foreach(config('stargate.resources') as $resource)
                            {
                                $this->blueprintMaker['finalShip']->$resource = 0;
                                if($partPrice[$resource] > 0)
                                    $this->blueprintMaker['finalShip']->$resource = $partPrice[$resource];
                            }
                        }

                        for($cptRes = 2; $cptRes < count($this->args); $cptRes += 2)
                        {
                            if(isset($this->args[$cptRes+1]))
                            {
                                if((int)$this->args[$cptRes+1] > 0)
                                    $qty = (int)$this->args[$cptRes+1];
                                else
                                    return trans('generic.wrongQuantity', [], $this->player->lang);

                                $shipPart = ShipPart::Where('slug', 'LIKE', $this->args[$cptRes].'%')->orWhere('id',$this->args[$cptRes])->first();
                                if(is_null($shipPart))
                                    return trans('shipyard.unknownShipPart', ['part' => $this->args[$cptRes]], $this->player->lang);

                                $hasRequirements = true;
                                foreach($shipPart->requiredTechnologies as $requiredTechnology)
                                {
                                    $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                                    if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                        $hasRequirements = false;
                                }
                                foreach($shipPart->requiredBuildings as $requiredBuilding)
                                {
                                    $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                                    if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                        $hasRequirements = false;
                                }

                                if(!$hasRequirements)
                                    return trans('generic.missingRequirements', [], $this->player->lang);

                                if(!isset($this->blueprintMaker[strtolower($shipPart->type)][$shipPart->id]))
                                    $this->blueprintMaker[strtolower($shipPart->type)][$shipPart->id] = ['part' => $shipPart, 'quantity' => $qty];

                                $partPrice = $shipPart->getPrice();
                                foreach(config('stargate.resources') as $resource)
                                {
                                    if(isset($partPrice[$resource]))
                                        $this->blueprintMaker['finalShip']->$resource += $partPrice[$resource]*$qty;
                                }

                                $this->blueprintMaker['finalShip']->capacity -= $shipPart->used_capacity*$qty;
                                if($this->blueprintMaker['finalShip']->capacity < 100)
                                    return trans('shipyard.missingFuelStorage', [], $this->player->lang); //min 100 d espace pour le fuel

                                switch($shipPart->type)
                                {
                                    case 'Armament':
                                        $this->blueprintMaker['finalShip']->fire_power += $shipPart->fire_power*$qty;
                                    break;
                                    case 'Shield':
                                        $this->blueprintMaker['finalShip']->shield += $shipPart->shield*$qty;
                                    break;
                                    case 'Hull':
                                        $this->blueprintMaker['finalShip']->hull += $shipPart->hull*$qty;
                                    break;
                                    case 'Reactor':
                                        $this->blueprintMaker['finalShip']->speed += $shipPart->speed*$qty;
                                        if($this->blueprintMaker['finalShip']->speed > 4 && $shipPart->id != 28 /**Lantean reactor*/)
                                            $this->blueprintMaker['finalShip']->speed = 4;
                                        elseif($this->blueprintMaker['finalShip']->speed > 5)
                                            $this->blueprintMaker['finalShip']->speed = 5;
                                    break;
                                }

                                $this->blueprintMaker['finalShip']->base_time += $shipPart->base_time*$qty;
                            }
                            else
                                return trans('generic.wrongParameter', [], $this->player->lang);
                        }

                        if($this->blueprintMaker['finalShip']->fire_power == 0
                        || $this->blueprintMaker['finalShip']->hull == 0
                        || $this->blueprintMaker['finalShip']->speed == 0)
                        return trans('generic.missingComponement', [], $this->player->lang); //indiquer les compo minimums

                        //Proposition de plan
                        $now = Carbon::now();
                        $componentEnd = $now->copy()->addSeconds($this->blueprintMaker['finalShip']->base_time);
                        $shipTime = $now->diffForHumans($componentEnd,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        $firePowerString = trans('shipyard.firePower', ['firepower' => config('stargate.emotes.armament').' '.number_format($this->blueprintMaker['finalShip']->fire_power)], $this->player->lang);
                        $shieldString = trans('shipyard.shield', ['shield' => config('stargate.emotes.shield').' '.number_format($this->blueprintMaker['finalShip']->shield)], $this->player->lang);
                        $hullString = trans('shipyard.hull', ['hull' => config('stargate.emotes.hull').' '.number_format($this->blueprintMaker['finalShip']->hull)], $this->player->lang);
                        $capacityString = trans('shipyard.capacity', ['capacity' => config('stargate.emotes.freight').' '.number_format($this->blueprintMaker['finalShip']->capacity)], $this->player->lang);
                        $crewString = trans('shipyard.crew', ['crew' => config('stargate.emotes.military').' '.number_format($this->blueprintMaker['finalShip']->crew)], $this->player->lang);
                        $speedString = trans('shipyard.speed', ['speed' => config('stargate.emotes.speed').' '.number_format($this->blueprintMaker['finalShip']->speed,2)], $this->player->lang);

                        $shipPrice = '';
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($this->blueprintMaker['finalShip']->$resource > 0)
                                $shipPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($this->blueprintMaker['finalShip']->$resource));
                        }

                        $shipModel = $firePowerString."\n".$shieldString."\n".$hullString."\n".$capacityString."\n".$speedString."\n".$crewString."\n".trans('generic.duration', [], $this->player->lang).": ".$shipTime."\n".trans('generic.price', [], $this->player->lang).": ".$shipPrice;

                        $compoString = '1x '.trans('shipyard.'.$this->blueprintMaker['blueprint']->slug.'.name', [], $this->player->lang)."\n";
                        foreach($this->blueprintMaker['armament'] as $element)
                            $compoString .= $element['quantity'].'x '.trans('shipyard.'.$element['part']->slug.'.name', [], $this->player->lang)."\n";
                        foreach($this->blueprintMaker['shield'] as $element)
                            $compoString .= $element['quantity'].'x '.trans('shipyard.'.$element['part']->slug.'.name', [], $this->player->lang)."\n";
                        foreach($this->blueprintMaker['hull'] as $element)
                            $compoString .= $element['quantity'].'x '.trans('shipyard.'.$element['part']->slug.'.name', [], $this->player->lang)."\n";
                        foreach($this->blueprintMaker['reactor'] as $element)
                            $compoString .= $element['quantity'].'x '.trans('shipyard.'.$element['part']->slug.'.name', [], $this->player->lang)."\n";

                        $modelConfirmationMessage = [
                            'author' => [
                                'name' => $this->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            "title" => trans('shipyard.modelCreation', [], $this->player->lang),
                            "description" => $shipModel,
                            'fields' => [array('name' => 'Composants', 'value' => $compoString)],
                            'footer' => array(
                                'text'  => 'Stargate',
                            ),
                        ];

                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage('', false, $modelConfirmationMessage)->then(function ($messageSent){

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
                                        $this->blueprintMaker['finalShip']->name = 'CustomShip'.rand(100,1000);
                                        $this->blueprintMaker['finalShip']->slug = Str::of($this->blueprintMaker['finalShip']->name)->slug();
                                        $this->blueprintMaker['finalShip']->player_id = $this->player->id;
                                        $this->blueprintMaker['finalShip']->save();

                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('shipyard.modelCreation', [], $this->player->lang),
                                                                                          'description' => trans('shipyard.newModelCreated', ['modelName' => $this->blueprintMaker['finalShip']->name, 'modelSlug' => $this->blueprintMaker['finalShip']->slug], $this->player->lang)]);
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.cancelled', [], $this->player->lang)]);
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
                    catch(\Exception $e)
                    {
                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                    }
                }
                elseif(Str::startsWith('parts', $this->args[0]))
                {
                    //Affiche les ship parts disponibles (tri par type, 1 page par catégorie)
                    $this->componentList = ShipPart::where('type','Blueprint')->get();
                    $this->componentSelectedType = 'Blueprint';

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->componentList->count()/5);
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getComponentsPage())->then(function ($messageSent){
                        $this->paginatorMessage = $messageSent;

                        $this->paginatorMessage->react('◀️')->then(function(){
                            $this->paginatorMessage->react('▶️')->then(function(){
                                $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.ship')))->then(function(){
                                    $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.armament')))->then(function(){
                                        $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.shield')))->then(function(){
                                            $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.hull')))->then(function(){
                                                $this->paginatorMessage->react(str_replace(array('<','>',),'',config('stargate.emotes.reactor')))->then(function(){
                                                    $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                                });
                                            });
                                        });
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
                                    elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                    {
                                        $this->page--;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getComponentsPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                    {
                                        $this->page++;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getComponentsPage());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == 'ship'
                                        || $messageReaction->emoji->name == 'armament'
                                        || $messageReaction->emoji->name == 'shield'
                                        || $messageReaction->emoji->name == 'hull'
                                        || $messageReaction->emoji->name == 'reactor')
                                    {
                                        switch($messageReaction->emoji->name)
                                        {
                                            case 'ship':
                                                $this->componentList = ShipPart::where('type','Blueprint')->get();
                                                $this->componentSelectedType = 'Blueprint';
                                            break;
                                            case 'armament':
                                                $this->componentList = ShipPart::where('type','Armament')->get();
                                                $this->componentSelectedType = 'Armament';
                                            break;
                                            case 'shield':
                                                $this->componentList = ShipPart::where('type','Shield')->get();
                                                $this->componentSelectedType = 'Shield';
                                            break;
                                            case 'hull':
                                                $this->componentList = ShipPart::where('type','Hull')->get();
                                                $this->componentSelectedType = 'Hull';
                                            break;
                                            case 'reactor':
                                                $this->componentList = ShipPart::where('type','Reactor')->get();
                                                $this->componentSelectedType = 'Reactor';
                                            break;
                                        }
                                        $this->page = 1;
                                        $this->maxPage = ceil($this->componentList->count()/5);
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getComponentsPage());
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
                }
                elseif(Str::startsWith('queue', $this->args[0]))
                {
                    echo PHP_EOL.'Execute Shipyard Queue';

                    if(count($this->args) > 1 && Str::startsWith('recycling', $this->args[1]))
                    {
                        if($this->player->activeColony->reyclingQueue->count() == 0)
                            return trans('shipyard.emptyRecyclingQueue', [], $this->player->lang);
                        $this->shipQueue = $this->player->activeColony->reyclingQueue;
                        $this->maxPage = ceil($this->reyclingQueue->count()/5);
                        $this->queueType = 'recycling';
                    }
                    else
                    {
                        if($this->player->activeColony->shipQueues->count() == 0)
                            return trans('shipyard.emptyQueue', [], $this->player->lang);
                        $this->shipQueue = $this->player->activeColony->shipQueues;
                        $this->maxPage = ceil($this->shipQueue->count()/5);
                        $this->queueType = 'building';
                    }

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxTime = time()+180;
                    $this->message->channel->sendMessage('', false, $this->getQueue())->then(function ($messageSent){
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
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                    {
                                        $this->page--;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                    {
                                        $this->page++;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
                                        $messageReaction->message->addEmbed($newEmbed);
                                    }
                                    elseif($messageReaction->emoji->name == '⏩')
                                    {
                                        $this->page = $this->maxPage;
                                        $newEmbed = $this->discord->factory(Embed::class,$this->getQueue());
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
                }
                else
                {
                    $ship = Ship::where([['player_id', $this->player->id],['slug', 'LIKE', $this->args[0].'%']])->first();
                    if(!is_null($ship))
                    {

                        if(count($this->args) >= 2 && Str::startsWith('recycle', $this->args[1]))
                        {
                            if(count($this->args) >= 3 && is_numeric($this->args[2]) && $this->args[2] > 0)
                                $qty = (int)$this->args[2];
                            else
                                return trans('generic.wrongQuantity', [], $this->player->lang);

                            if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                                return trans('generic.busyBuilding', [], $this->player->lang);

                            if($this->player->activeColony->reyclingQueue->count() >= 5)
                                return trans('shipyard.recyclingQueueIsFull', [], $this->player->lang);

                            if(($this->player->activeColony->reyclingQueue->count() + $qty) > 5)
                                $qty = 5 - $this->player->activeColony->reyclingQueue->count();

                            $shipCheck = $this->player->activeColony->hasShipById($ship->id);
                            if($shipCheck)
                            {
                                if($qty > $shipCheck->pivot->number)
                                {
                                    $missingResString = " ".$ship->name.": ".number_format($qty-$shipCheck->pivot->number);
                                        return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);
                                }
                                else
                                {
                                    //CONFIRM
                                    $rerollConfirm = trans('shipyard.recyclingConfirm', ['shipName' => $ship->name, 'qty' => $qty], $this->player->lang);
                                    $embed = [
                                        'author' => [
                                            'name' => $this->player->user_name,
                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                        ],
                                        //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/rerollGif1.gif'], //GIF A TROUVER
                                        "title" => "Stargate",
                                        "description" => $rerollConfirm,
                                        'fields' => [
                                        ],
                                        'footer' => array(
                                            'text'  => 'Stargate',
                                        ),
                                    ];
                                    $newEmbed = $this->discord->factory(Embed::class,$embed);

                                    $this->maxTime = time()+180;
                                    $this->message->channel->sendMessage('', false, $newEmbed)->then(function ($messageSent) use($shipCheck,$qty){

                                        $this->closed = false;
                                        $this->paginatorMessage = $messageSent;
                                        $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                            $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                            });
                                        });

                                        $filter = function($messageReaction){
                                            return $messageReaction->user_id == $this->player->user_id;
                                        };
                                        $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($shipCheck,$qty){
                                            $messageReaction = $collector->first();
                                            try{
                                                if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                                {
                                                    $cannotReycleString = '';
                                                    $this->player->activeColony->ships();
                                                    $this->player->activeColony->load('reyclingQueue');

                                                    if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                                                        $cannotReycleString = trans('generic.busyBuilding', [], $this->player->lang);

                                                    if($this->player->activeColony->reyclingQueue->count() >= 5)
                                                        $cannotReycleString = trans('shipyard.recyclingQueueIsFull', [], $this->player->lang);

                                                    if(($this->player->activeColony->reyclingQueue->count() + $qty) > 5)
                                                        $qty = 5 - $this->player->activeColony->reyclingQueue->count();

                                                    $shipCheck = $this->player->activeColony->hasShipById($shipCheck->id);
                                                    if($shipCheck)
                                                    {
                                                        if($qty > $shipCheck->pivot->number)
                                                        {
                                                            $missingResString = " ".$shipCheck->name.": ".number_format($qty-$shipCheck->pivot->number);
                                                            $cannotReycleString = trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);
                                                        }
                                                    }

                                                    if(!empty($cannotReycleString))
                                                    {
                                                        $newEmbed = $this->discord->factory(Embed::class,[
                                                            'title' => trans('generic.cancelled', [], $this->player->lang),
                                                            "description" => $cannotReycleString
                                                            ]);
                                                        $messageReaction->message->addEmbed($newEmbed);
                                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                                        return;
                                                    }

                                                    $shipCheck->pivot->number -= $qty;
                                                    if($shipCheck->pivot->number <= 0)
                                                        $this->player->activeColony->ships()->detach($shipCheck->id);
                                                    else
                                                        $shipCheck->pivot->save();

                                                    $now = Carbon::now();
                                                    $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startRecyclingShip($shipCheck,$qty));
                                                    $buildingTime = $now->diffForHumans($endingDate,[
                                                        'parts' => 3,
                                                        'short' => true, // short syntax as per current locale
                                                        'syntax' => CarbonInterface::DIFF_ABSOLUTE
                                                    ]);

                                                    $embed = [
                                                        'author' => [
                                                            'name' => $this->player->user_name,
                                                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                                                        ],
                                                        //'image' => ["url" => 'http://bot.thorr.ovh/stargate/laravel/public/images/rerollGif2.gif'],
                                                        "title" => "Stargate",
                                                        "description" => trans('shipyard.reyclingStarted', ['shipName' => $shipCheck->name, 'qty' => $qty, 'time' => $buildingTime], $this->player->lang),
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
                                                    $newEmbed = $this->discord->factory(Embed::class,[
                                                        'title' => trans('generic.cancelled', [], $this->player->lang)
                                                        ]);
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
                                    return;
                                }
                            }
                            else
                            {
                                $missingResString = " ".$ship->name.": ".number_format($qty);
                                return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);
                            }
                            return;
                        }

                        //Requirement
                        $hasRequirements = true;

                        $blueprintTechnology = Technology::find(6);
                        $currentBlueprintLvl = $this->player->hasTechnology($blueprintTechnology);
                        if(!$currentBlueprintLvl || $currentBlueprintLvl < $ship->required_blueprint)
                            $hasRequirements = false;

                        $shipyard = Building::find(9); // Shipyard
                        $currentShipyardLvl = $this->player->activeColony->hasBuilding($shipyard);
                        if(!$currentShipyardLvl || $currentShipyardLvl < $ship->required_shipyard)
                            $hasRequirements = false;

                        if(!$hasRequirements)
                            return trans('generic.missingRequirements', [], $this->player->lang);

                        if(count($this->args) >= 2 && is_numeric($this->args[1]) && $this->args[1] > 0)
                            $qty = (int)$this->args[1];
                        else
                            return trans('generic.wrongQuantity', [], $this->player->lang);

                        $hasEnough = true;

                        $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Ship']);

                        $shipPrices = $ship->getPrice($qty, $coef);

                        $missingResString = "";
                        foreach (config('stargate.resources') as $resource)
                        {
                            if($ship->$resource > 0 && $shipPrices[$resource] > $this->player->activeColony->$resource)
                            {
                                $hasEnough = false;
                                $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($shipPrices[$resource]-$this->player->activeColony->$resource));
                            }
                        }
                        if(!$hasEnough)
                            return trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);

                        if(!is_null($this->player->activeColony->active_building_id) && $this->player->activeColony->active_building_id == 9)
                        return trans('generic.busyBuilding', [], $this->player->lang);

                        $now = Carbon::now();
                        $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->activeColony->startShip($ship,$qty));
                        $buildingTime = $now->diffForHumans($endingDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('shipyard.buildingStarted', ['name' => $ship->name, 'qty' => $qty, 'time' => $buildingTime], $this->player->lang);
                    }
                    else
                        return trans('shipyard.unknownShip', [], $this->player->lang);
                }
            }
            catch(\Exception $e)
            {
                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }

    public function getComponentsPage()
    {
        $displayList = $this->componentList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('shipyard.componentList', [], $this->player->lang),
            "description" => '',
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $component)
        {
            $componentPrice = "";
            $componentPrices = $component->getPrice();
            foreach (config('stargate.resources') as $resource)
            {
                if($component->$resource > 0)
                {
                    $componentPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($componentPrices[$resource]));

                    if($this->player->activeColony->$resource >= ceil($componentPrices[$resource]))
                        $componentPrice .= ' '.config('stargate.emotes.confirm');
                    else
                        $componentPrice .= ' '.config('stargate.emotes.cancel');
                }
            }
            $componentBaseTime = $component->base_time;

            $now = Carbon::now();
            $componentEnd = $now->copy()->addSeconds($componentBaseTime);
            $componentTime = $now->diffForHumans($componentEnd,[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);

            $hasRequirements = true;
            foreach($component->requiredTechnologies as $requiredTechnology)
            {
                $currentLvlOwned = $this->player->hasTechnology($requiredTechnology);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                    $hasRequirements = false;
            }
            foreach($component->requiredBuildings as $requiredBuilding)
            {
                $currentLvlOwned = $this->player->activeColony->hasBuilding($requiredBuilding);
                if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                    $hasRequirements = false;
            }
            if($hasRequirements == true)
            {
                $firePowerString = $shieldString = $hullString = $capacityString = $crewString = $speedString = $usedCapacityString = '';
                if($component->fire_power > 0)
                    $firePowerString = trans('shipyard.firePower', ['firepower' => config('stargate.emotes.armament').' '.number_format($component->fire_power)], $this->player->lang)."\n";
                if($component->shield > 0)
                    $shieldString = trans('shipyard.shield', ['shield' => config('stargate.emotes.shield').' '.number_format($component->shield)], $this->player->lang)."\n";
                if($component->hull > 0)
                    $hullString = trans('shipyard.hull', ['hull' => config('stargate.emotes.hull').' '.number_format($component->hull)], $this->player->lang)."\n";
                if($component->capacity > 0)
                    $capacityString = trans('shipyard.capacity', ['capacity' => config('stargate.emotes.freight').' '.number_format($component->capacity)], $this->player->lang)."\n";
                if($component->crew > 0)
                    $crewString = trans('shipyard.crew', ['crew' => config('stargate.emotes.military').' '.number_format($component->crew)], $this->player->lang)."\n";
                if($component->speed > 0)
                    $speedString = trans('shipyard.speed', ['speed' => config('stargate.emotes.speed').' '.number_format($component->speed,2)], $this->player->lang)."\n";
                if($component->used_capacity > 0)
                    $usedCapacityString = trans('shipyard.usedCapacity', ['usedCapacity' => config('stargate.emotes.freight').' '.$component->used_capacity], $this->player->lang)."\n";

                $embed['fields'][] = array(
                    'name' => $component->id.' - '.trans('shipyard.'.$component->slug.'.name', [], $this->player->lang),
                    'value' => "\nSlug: `".$component->slug."`\n".
                               $firePowerString.$shieldString.$hullString.$capacityString.$speedString.$crewString.$usedCapacityString.
                               trans('generic.duration', [], $this->player->lang).": ".$componentTime."\n".
                               trans('generic.price', [], $this->player->lang).": ".$componentPrice."\n",
                    'inline' => true
                );
            }
            else
            {
                $requirementString = '';
                foreach($component->requiredTechnologies as $requiredTechnology)
                {
                    $techLevel = $this->player->hasTechnology($requiredTechnology);
                    if(!$techLevel)
                        $techLevel = 0;

                    $requirementString .= trans('research.'.$requiredTechnology->slug.'.name', [], $this->player->lang)." Lvl ".$requiredTechnology->pivot->level." (".$techLevel;
                    if($techLevel >= $requiredTechnology->pivot->level)
                        $requirementString .= ' '.config('stargate.emotes.confirm').")\n";
                    else
                        $requirementString .= ' '.config('stargate.emotes.cancel').")\n";
                }
                foreach($component->requiredBuildings as $requiredBuilding)
                {
                    $buildLvl = $this->player->activeColony->hasBuilding($requiredBuilding);
                    if(!$buildLvl)
                        $buildLvl = 0;
                    $requirementString .= trans('building.'.$requiredBuilding->slug.'.name', [], $this->player->lang)." Lvl ".$requiredBuilding->pivot->level." (".$buildLvl;
                    if($buildLvl >= $requiredBuilding->pivot->level)
                        $requirementString .= ' '.config('stargate.emotes.confirm').")\n";
                    else
                        $requirementString .= ' '.config('stargate.emotes.cancel').")\n";
                }

                $embed['fields'][] = array(
                    'name' => $component->id.' - '.trans('shipyard.'.$component->slug.'.name', [], $this->player->lang),
                    'value' => "\n".$requirementString,
                    'inline' => true
                );
            }
        }

        return $embed;
    }


    public function getPage()
    {
        try{
            $displayList = $this->shipList->skip(5*($this->page -1))->take(5);

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('shipyard.shipList', [], $this->player->lang),
                "description" => trans('shipyard.howTo', [], $this->player->lang),
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
                ),
            ];
            $blueprintTechnology = Technology::find(6);
            $shipyard = Building::find(9); // Shipyard
            $armamentTec = Technology::Where('slug', 'LIKE', 'armament')->first();
            $armamentLvl = $this->player->hasTechnology($armamentTec);
            $shieldTec = Technology::Where('slug', 'LIKE', 'shield')->first();
            $shieldLvl = $this->player->hasTechnology($shieldTec);
            $hullTec = Technology::Where('slug', 'LIKE', 'hull')->first();
            $hullLvl = $this->player->hasTechnology($hullTec);

            foreach($displayList as $ship)
            {
                $shipPrice = "";

                $coef = $this->player->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Ship']);

                $shipPrices = $ship->getPrice(1, $coef);
                foreach (config('stargate.resources') as $resource)
                {
                    if($ship->$resource > 0)
                    {
                        $shipPrice .= "\n".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($shipPrices[$resource]));

                        if($this->player->activeColony->$resource >= ceil($shipPrices[$resource]))
                            $shipPrice .= ' '.config('stargate.emotes.confirm');
                        else
                            $shipPrice .= ' '.config('stargate.emotes.cancel');
                    }
                }
                $shipTime = $ship->base_time;

                /** Application des bonus */
                $shipTime *= $this->player->activeColony->getShipbuildBonus();

                $now = Carbon::now();
                $shipEnd = $now->copy()->addSeconds($shipTime);
                $shipTime = $now->diffForHumans($shipEnd,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);

                $hasRequirements = true;
                $requirementString = '';
                $currentBlueprintLvl = $this->player->hasTechnology($blueprintTechnology);
                $currentShipyardLvl = $this->player->activeColony->hasBuilding($shipyard);
                if((!$currentBlueprintLvl || $currentBlueprintLvl < $ship->required_blueprint) || (!$currentShipyardLvl || $currentShipyardLvl < $ship->required_shipyard))
                {
                    $hasRequirements = false;
                    if(!$currentBlueprintLvl)
                        $currentBlueprintLvl = 0;
                    $requirementString .= trans('research.'.$blueprintTechnology->slug.'.name', [], $this->player->lang)." Lvl ".$ship->required_blueprint." (".$currentBlueprintLvl;
                    if($currentBlueprintLvl >= $ship->required_blueprint)
                        $requirementString .= ' '.config('stargate.emotes.confirm').")\n";
                    else
                        $requirementString .= ' '.config('stargate.emotes.cancel').")\n";
                    if(!$currentShipyardLvl)
                        $currentShipyardLvl = 0;
                    $requirementString .= trans('building.'.$shipyard->slug.'.name', [], $this->player->lang)." Lvl ".$ship->required_shipyard." (".$currentShipyardLvl;
                    if($currentShipyardLvl >= $ship->required_shipyard)
                        $requirementString .= ' '.config('stargate.emotes.confirm').")\n";
                    else
                        $requirementString .= ' '.config('stargate.emotes.cancel').")\n";
                }

                $ownedUnits = $this->player->activeColony->hasShip($ship);
                if(!$ownedUnits)
                    $ownedUnits = 0;

                if($hasRequirements)
                {
                    $firePower = $ship->fire_power;
                    if($armamentLvl)
                        $firePower *= pow(1.1,$armamentLvl);
                    $shield = $ship->shield;
                    if($shieldLvl)
                        $shield *= pow(1.1,$shieldLvl);
                    $hull = $ship->hull;
                    if($hullLvl)
                        $hull *= pow(1.1,$hullLvl);

                    $speedBonus = $this->player->getShipSpeedBonus();

                    $firePowerString = trans('shipyard.firePower', ['firepower' => config('stargate.emotes.armament').' '.number_format($firePower)], $this->player->lang);
                    $shieldString = trans('shipyard.shield', ['shield' => config('stargate.emotes.shield').' '.number_format($shield)], $this->player->lang);
                    $hullString = trans('shipyard.hull', ['hull' => config('stargate.emotes.hull').' '.number_format($hull)], $this->player->lang);
                    $capacityString = trans('shipyard.capacity', ['capacity' => config('stargate.emotes.freight').' '.number_format($ship->capacity)], $this->player->lang);
                    $crewString = trans('shipyard.crew', ['crew' => config('stargate.emotes.military').' '.number_format($ship->crew)], $this->player->lang);
                    $speedString = trans('shipyard.speed', ['speed' => config('stargate.emotes.speed').' '.number_format($ship->speed*$speedBonus,2)], $this->player->lang);

                    $embed['fields'][] = array(
                        'name' => $ship->name.' ('.number_format($ownedUnits).')',
                        'value' => "\nSlug: `".$ship->slug."`\n - ".
                                trans('generic.duration', [], $this->player->lang).": ".$shipTime."\n".trans('generic.price', [], $this->player->lang).": ".
                                $shipPrice."\n".$firePowerString."\n".$shieldString."\n".$hullString."\n".$capacityString."\n".$speedString."\n".$crewString,
                        'inline' => true
                    );
                }
                else
                {
                    $embed['fields'][] = array(
                        'name' => $ship->name.' ('.number_format($ownedUnits).')',
                        'value' => $requirementString,
                        'inline' => true
                    );
                }
            }

            return $embed;
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function getQueue()
    {
        try
        {
            $displayList = $this->shipQueue->skip(5*($this->page -1))->take(5);

            $shipQueueString = "";
            foreach($displayList as $queuedShip)
            {
                $now = Carbon::now();
                $buildTime = $now->diffForHumans($queuedShip->pivot->ship_end,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);

                $shipQueueString .= "1x ".$queuedShip->name." - ".$buildTime."\n";
            }

            switch($this->queueType)
            {
                case 'recycling':
                    $embedTitle = trans('shipyard.shipRecyclingQueue', [], $this->player->lang);
                break;
                case 'building':
                    $embedTitle = trans('shipyard.shipQueue', [], $this->player->lang);
                default:
                break;
            }

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => $embedTitle,
                "description" => $shipQueueString,
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
                ),
            ];

            return $embed;
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

}
