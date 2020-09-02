<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Exploration extends Model
{
    public function colonySource()
    {
        return $this->hasOne('App\Colony','id','colony_source_id');
    }

    public function coordinateDestination()
    {
        return $this->hasOne('App\Coordinate','id','coordinate_destination_id');
    }

    public function player(){
        return $this->belongsTo('App\Player');
    }

    public function outcome()
    {
        $randomEvent = rand(1,100);

        if($randomEvent <= 30)
        {
            $this->exploration_result = false;
            $this->save();

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreFailed', ['coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        elseif($randomEvent <= 38)
        {
            //Building
            $buildings = Building::all();
            $filtredBuildings =  $buildings->filter(function ($value){
                                    return $value->requiredTechnologies->count() > 0 || $value->requiredBuildings->count() > 0;
                                });

            $randomBuilding = $filtredBuildings->random();

            $requirementString = "";
            foreach($randomBuilding->requiredTechnologies as $requiredTechnology)
                $requirementString .= "Lvl ".$requiredTechnology->pivot->level." - ".trans('research.'.$requiredTechnology->slug.'.name', [], $this->player->lang)."\n";
            foreach($randomBuilding->requiredBuildings as $requiredBuilding)
                $requirementString .= "Lvl ".$requiredBuilding->pivot->level." - ".trans('building.'.$requiredBuilding->slug.'.name', [], $this->player->lang)."\n";
                    
            $this->exploration_result = true;
            $this->exploration_outcome = 'Tip';
            $this->save();

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessBuildingTip', ['name' => trans('building.'.$randomBuilding->slug.'.name', [], $this->player->lang), 'requirements' => $requirementString, 'coordinates' => $this->coordinateDestination->galaxy.':'.$this->coordinateDestination->system.':'.$this->coordinateDestination->planet], $this->player->lang);
        }
        elseif($randomEvent <= 46)
        {
            //Technology
            $technologies = Technology::all();
            $filtredTechnologies =  $technologies->filter(function ($value){
                                    return $value->requiredTechnologies->count() > 0 || $value->requiredBuildings->count() > 0;
                                });

            $randomTechnology = $filtredTechnologies->random();

            $requirementString = "";
            foreach($randomTechnology->requiredTechnologies as $requiredTechnology)
                $requirementString .= "Lvl ".$requiredTechnology->pivot->level." - ".trans('research.'.$requiredTechnology->slug.'.name', [], $this->player->lang)."\n";
            foreach($randomTechnology->requiredBuildings as $requiredBuilding)
                $requirementString .= "Lvl ".$requiredBuilding->pivot->level." - ".trans('building.'.$requiredBuilding->slug.'.name', [], $this->player->lang)."\n";

            $this->exploration_result = true;
            $this->exploration_outcome = 'Tip';
            $this->save();

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessTechnologyTip', ['name' => trans('research.'.$randomTechnology->slug.'.name', [], $this->player->lang), 'requirements' => $requirementString, 'coordinates' => $this->coordinateDestination->galaxy.':'.$this->coordinateDestination->system.':'.$this->coordinateDestination->planet], $this->player->lang);
        }
        elseif($randomEvent <= 54)
        {
            //Craft
            $units = Unit::all();
            $randomUnit = $units->random();

            $requirementString = "";
            foreach($randomUnit->requiredTechnologies as $requiredTechnology)
                $requirementString .= "Lvl ".$requiredTechnology->pivot->level." - ".trans('research.'.$requiredTechnology->slug.'.name', [], $this->player->lang)."\n";
            foreach($randomUnit->requiredBuildings as $requiredBuilding)
                $requirementString .= "Lvl ".$requiredBuilding->pivot->level." - ".trans('building.'.$requiredBuilding->slug.'.name', [], $this->player->lang)."\n";
                
            $this->exploration_result = true;
            $this->exploration_outcome = 'Tip';
            $this->save();

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessCraftTip', ['name' => trans('craft.'.$randomUnit->slug.'.name', [], $this->player->lang), 'requirements' => $requirementString, 'coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        elseif($randomEvent <= 62)
        {
            //Craft aléatoire
            $randomUnit = Unit::all()->random();
            $resValue = rand(1,4);
            $resourceString = ucfirst(trans('craft.'.$randomUnit->slug.'.name', [], $this->player->lang)).': '.number_format($resValue);

            $this->exploration_result = true;
            $this->exploration_outcome = 'Unit';
            $this->unit_id = $randomUnit->id;
            $this->outcome_quantity = $resValue;
            $this->save();

            $unitExists = $this->colonySource->units->filter(function ($value) use($randomUnit){               
                return $value->id == $randomUnit->id;
            });
            if($unitExists->count() > 0)
            {
                $unitToUpdate = $unitExists->first();
                $unitToUpdate->pivot->number += $resValue;
                $unitToUpdate->pivot->save();
            }
            else
            {
                $this->colonySource->units()->attach([$randomUnit->id => ['number' => $resValue]]);
            }

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessResources', ['resources' => $resourceString, 'coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        /*elseif($randomEvent <= 60)
        {
            //defence TIP
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
            elseif($randomRes < 75)
                $resType = 'gold';
            elseif($randomRes < 90)
                $resType = 'quartz';
            else
                $resType = 'naqahdah';

            $varProd = 'production_'.$resType;

            if($resType == 'E2PZ')
                $resValue = rand(1,5);
            else
                $resValue = $this->colonySource->$varProd * rand(1,6);
            $resourceString = config('stargate.emotes.'.strtolower($resType))." ".ucfirst($resType).': '.number_format($resValue);

            $this->exploration_result = true;
            $this->exploration_outcome = 'Resource';
            $this->outcome_resource = $resType;
            $this->outcome_quantity = $resValue;
            $this->save();

            $this->colonySource->$resType += $resValue;
            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessResources', ['resources' => $resourceString, 'coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        else
        {
            $this->exploration_result = false;
            $this->save();
            return trans('stargate.exploreCriticalFailed', ['coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
    }
}
