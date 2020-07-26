<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Utility\TopUpdater;

class Player extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::updating(function($player) {
            //dd($player);
        });
    }

    public function colonies()
    {
        return $this->hasMany('App\Colony');
    }

    public function commandLogs()
    {
        return $this->hasMany('App\CommandLogs');
    }

    public function technologies()
    {
        return $this->belongsToMany('App\Technology')->withPivot('level');
    }

    public function activeTechnology()
    {
        return $this->hasOne('App\Technology','id','active_technology_id');
    }

    public function addColony($first = true)
    {
        $newColony = new Colony;
        $newColony->colony_type = 1;
        $newColony->player_id = $this->id;
        $newColony->name = 'P'.rand(1, 9).Str::upper(Str::random(1)).'-'.rand(1, 9).rand(1, 9).rand(1, 9);
        if($first)
            $newColony->space_max = 180;
        else
            $newColony->space_max = rand(100,250);
        $newColony->last_claim = date("Y-m-d H:i:s");
        $newColony->save();

        $this->colonies->push($newColony);
    }

    public function hasTechnology(Technology $technology)
    {
        try{
            $technologyExist = $this->technologies->filter(function ($value) use($technology){
                return $value->id == $technology->id;
            });
            if($technologyExist->count() > 0)
            {
                $foundTechology = $technologyExist->first();
                return $foundTechology->pivot->level;
            }
            else
                return false;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function startResearch(Technology $technology)
    {
        $current = Carbon::now();
        $wantedLvl = 1;
        $currentLevel = $this->hasTechnology($technology);
        if($currentLevel)
            $wantedLvl += $currentLevel;

        //Temps de base
        $buildingTime = $technology->getTime($wantedLvl);

        /** Application des bonus */
        $buildingTime *= $this->getResearchBonus();
        $buildingTime *= $this->colonies[0]->getResearchBonus();

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_technology_id = $technology->id;
        $this->active_technology_end = $buildingEnd;

        $buildingPrices = $technology->getPrice($wantedLvl);
        foreach (config('stargate.resources') as $resource)
        {
            if($technology->$resource > 0)
                $this->colonies[0]->$resource -= round($buildingPrices[$resource]);
        }
        //$this->colonies[0]->save();
        $this->save();
        return $this->active_technology_end;
    }

    public function checkTechnology()
    {
        echo PHP_EOL.'CHECK_TECHNOLOGY';
        if(!is_null($this->active_technology_end))
        {
            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->active_technology_end);
            if($endingDate->isPast())
            {
                $this->technologyIsDone($this->activeTechnology);
            }
        }
    }

    public function technologyIsDone(Technology $technology)
    {
        try{
            $technologyExists = $this->technologies->filter(function ($value) use($technology){               
                return $value->id == $technology->id;
            });
            if($technologyExists->count() > 0)
            {
                $technologyToUpgrade = $technologyExists->first();
                $technologyToUpgrade->pivot->level++;
                $technologyToUpgrade->pivot->save();
            }
            else
            {
                $this->technologies()->attach([$technology->id => ['level' => 1]]);
            }

            $this->active_technology_id = null;
            $this->active_technology_end = null;
            $this->colonies[0]->calcProd();
            $this->colonies[0]->saveWithoutEvents();
            $this->save();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }


    public function getResearchBonus()
    {
        $bonus = 1;

        $buildings = $this->colonies[0]->buildings->filter(function ($value){
            return !is_null($value->technology_bonus);
        });
        foreach($buildings as $building)
            $bonus *= round(pow($building->technology_bonus, $building->pivot->level));

        $technologies = $this->technologies->filter(function ($value){
            return !is_null($value->technology_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= round(pow($technology->technology_bonus, $technology->pivot->level));

        return $bonus;
    }
    public function getBuildingBonus()
    {
        $bonus = 1;

        $buildings = $this->colonies[0]->buildings->filter(function ($value){
            return !is_null($value->building_bonus);
        });
        foreach($buildings as $building)
            $bonus *= round(pow($building->building_bonus, $building->pivot->level));

        $technologies = $this->technologies->filter(function ($value){
            return !is_null($value->building_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= round(pow($technology->building_bonus, $technology->pivot->level));

        return $bonus;
    }

    
}
