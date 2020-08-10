<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Building;
use App\Technology;
use App\Coordinate;
use App\Unit;


class Stargate extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            try{
                echo PHP_EOL.'Execute Stargate';
                if($this->player->ban)
                    return trans('generic.banned', [], $this->player->lang);

                $researchCenter = Building::find(7);
                $centerLevel = $this->player->colonies[0]->hasBuilding($researchCenter);
                if(!$centerLevel || $centerLevel < 5)
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
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
                    $this->message->channel->sendMessage('', false, $embed);
                    return;
                }
                
                if(count($this->args) < 2)
                {
                    $embed = [
                        'author' => [
                            'name' => $this->player->user_name,
                            'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
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
                    $this->message->channel->sendMessage('', false, $embed);
                    return;
                }
                if(!preg_match('/[0-9]{1,}:[0-9]{1,}:[0-9]{1,}/', $this->args[1], $coordinatesMatch))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                //Est-ce que la destination à une porte ?
                $coordinates = explode(':',$coordinatesMatch[0]);
                $coordinate = Coordinate::where([["galaxy", $coordinates[0]], ["system", $coordinates[1]], ["planet", $coordinates[2]]])->first();

                if(is_null($coordinate))
                    return trans('stargate.unknownCoordinates', [], $this->player->lang);

                if(!is_null($coordinate->colony))
                {
                    $researchCenter = Building::find(7);
                    $centerLevel = $coordinate->colony->hasBuilding($researchCenter);
                    if(!$centerLevel || $centerLevel < 4)
                        return trans('stargate.failedDialing', [], $this->player->lang);
                }

                //Check Consommation E2PZ
                $travelCost = $this->getConsumption($this->player->colonies[0]->coordinates,$coordinate);
                if($this->player->colonies[0]->E2PZ < $travelCost)
                    return trans('generic.notEnoughResources', ['missingResources' => config('stargate.emotes.e2pz')." ".trans('generic.e2pz', [], $this->player->lang).': '.round($travelCost-$this->player->colonies[0]->E2PZ,2)], $this->player->lang);






                if($this->player->user_id != 125641223544373248)
                    return 'Commande en test';



                /**
                 * 
                 * 
                 * TODO
                 * 
                 * Message "Exploration en cours, elle prendra x minutes"
                 * 
                 * Transférer dans utility tout le bouzin
                 * 
                 * CHECK SI COORDONES destination = arrivée => erreur
                 * 
                 * Check 1000 troupes à envoyer
                 * Ajouter les ressources à l'inventaire
                 * Ajouter les crafts à l'inventaire
                 * Logs
                 * Durée aléatoire
                 * Lorsque explo finie, envoyer le message
                 * 1 explo à la fois
                 * retirer les troupes si mission failed
                 * 
                 * 
                 */




                if(Str::startsWith('explore',$this->args[0]))
                {
                    if(!is_null($coordinate->colony))
                        return trans('stargate.explorePlayerImpossible', [], $this->player->lang);

                    $randomEvent = rand(1,100);

                    if($randomEvent <= 30)
                    {
                        return trans('stargate.exploreFailed', ['coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }
                    elseif($randomEvent <= 37)
                    {
                        //Building
                        $buildings = Building::all();
                        $filtredBuildings =  $buildings->filter(function ($value){
                                                return $value->requiredTechnologies->count() > 0 || $value->requiredBuildings->count() > 0;
                                            });

                        $randomBuilding = $filtredBuildings->random();

                        $randomTip = rand(1,100);
                        if(($randomTip % 2 == 0 && $randomBuilding->requiredTechnologies->count() > 0) || $randomBuilding->requiredBuildings->count() == 0)
                            $randomRequirement = $randomBuilding->requiredTechnologies->random();
                        else
                            $randomRequirement = $randomBuilding->requiredBuildings->random();
                        
                        return trans('stargate.exploreSucessBuildingTip', ['name' => $randomBuilding->name, 'lvlRequirement' => $randomRequirement->pivot->level, 'nameRequirement' => $randomRequirement->name, 'coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }
                    elseif($randomEvent <= 44)
                    {
                        //Technology
                        $technologies = Technology::all();
                        $filtredTechnologies =  $technologies->filter(function ($value){
                                                return $value->requiredTechnologies->count() > 0 || $value->requiredBuildings->count() > 0;
                                            });

                        $randomTechnology = $filtredTechnologies->random();

                        $randomTip = rand(1,100);
                        if(($randomTip % 2 == 0 && $randomTechnology->requiredTechnologies->count() > 0) || $randomTechnology->requiredBuildings->count() == 0)
                            $randomRequirement = $randomTechnology->requiredTechnologies->random();
                        else
                            $randomRequirement = $randomTechnology->requiredBuildings->random();
                        return trans('stargate.exploreSucessTechnologyTip', ['name' => $randomTechnology->name, 'lvlRequirement' => $randomRequirement->pivot->level, 'nameRequirement' => $randomRequirement->name, 'coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }
                    elseif($randomEvent <= 52)
                    {
                        //Craft
                        $units = Unit::all();
                        $randomUnit = $units->random();

                        $randomTip = rand(1,100);
                        if(($randomTip % 2 == 0 && $randomUnit->requiredTechnologies->count() > 0) || $randomUnit->requiredBuildings->count() == 0)
                            $randomRequirement = $randomUnit->requiredTechnologies->random();
                        else
                            $randomRequirement = $randomUnit->requiredBuildings->random();
                        return trans('stargate.exploreSucessCraftTip', ['name' => $randomUnit->name, 'lvlRequirement' => $randomRequirement->pivot->level, 'nameRequirement' => $randomRequirement->name, 'coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }
                    elseif($randomEvent <= 60)
                    {
                        //Craft aléatoire
                        $randomUnit = Unit::all()->random();
                        $resValue = rand(1,3);
                        $resourceString = ucfirst($randomUnit->name).': '.number_format($resValue);
                        return trans('stargate.exploreSucessResources', ['resources' => $resourceString, 'coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }
                    /*elseif($randomEvent <= 60)
                    {
                        //Defense TIP
                        return trans('stargate.exploreSucess', ['tip' => ''], $this->player->lang);
                        //Vos scientifiques ont trouvé l'information suivante en explorant la planète [2:10:4]
                    }
                    elseif($randomEvent <= 75)
                    {
                        //Ship Componement TIP
                        return trans('stargate.exploreSucess', ['tip' => ''], $this->player->lang);
                        //Vos scientifiques ont trouvé l'information suivante en explorant la planète [2:10:4]
                    }*/
                    elseif($randomEvent <= 95)
                    {
                        //Ressource aléatoire
                        $randomRes = rand(1,100);
                        if($randomRes < 10)
                            $resType = 'E2PZ';
                        elseif($randomRes < 50)
                            $resType = 'iron';
                        elseif($randomRes < 70)
                            $resType = 'gold';
                        elseif($randomRes < 90)
                            $resType = 'quartz';
                        else
                            $resType = 'naqahdah';

                        if($resType == 'E2PZ')
                            $resValue = rand(1,10);
                        else
                            $resValue = rand(1000,10000);
                        $resourceString = config('stargate.emotes.'.$resType)." ".ucfirst($resType).': '.number_format($resValue);
                        return trans('stargate.exploreSucessResources', ['resources' => $resourceString, 'coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }
                    else
                    {
                        return trans('stargate.exploreCriticalFailed', ['coordinates' => $coordinate->galaxy.':'.$coordinate->system.':'.$coordinate->planet], $this->player->lang);
                    }

                }

                if(Str::startsWith('trade',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.tradeNpcImpossible', [], $this->player->lang);

                    return 'Under developement';
                }

                if(Str::startsWith('spy',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);

                    return 'Under developement';
                }
                
                if(Str::startsWith('attack',$this->args[0]))
                {
                    if(is_null($coordinate->colony))
                        return trans('stargate.neverExploredWorld', [], $this->player->lang);
                        
                    return 'Under developement';
                }
            }
            catch(\Exception $e)
            {
                return $e->getMessage();
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
            return abs($source->galaxy - $destination->galaxy)*3;
        else
            return abs($source->system - $destination->system)*0.03;
    }
}
