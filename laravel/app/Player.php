<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

    public function addColony()
    {
        $newColony = new Colony;
        $newColony->colony_type = 1;
        $newColony->player_id = $this->id;
        $newColony->name = 'P'.rand(1, 9).Str::upper(Str::random(1)).'-'.rand(1, 9).rand(1, 9).rand(1, 9);
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

    public function startTechnology(Technology $technology)
    {
        $current = Carbon::now();
        $levelWanted = 1;
        $currentLevel = $this->hasTechnology($technology);
        if($currentLevel)
            $levelWanted += $currentLevel;

        //Temps de base
        $buildingTime = $technology->getTime($levelWanted);

        /** Application des bonus */
        $buildingTime *= $this->getResearchBonus();

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_technology_id = $technology->id;
        $this->active_technology_end = $buildingEnd;
        $this->save();
        return $this->active_technology_end;
    }

    public function checkTechnology()
    {
        echo PHP_EOL.'CHECK_TECHNOLOGY';
        if(!is_null($this->active_building_end))
        {
            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->active_building_end);
            if($endingDate->isPast())
            {
                $this->builingdIsDone($this->activeBuilding);
            }
        }
    }

    public function getResearchBonus()
    {
        $researchBonus = 1;

        /** Bonus Informatique et Communication -5% */
        $informationTechnology = $this->hasTechnology(Technology::find(1));
        if($informationTechnology)
            $researchBonus *= pow(0.95, $informationTechnology);

        /** Bonus Centre de recherche -10% */
        $researchCenterLevel = $this->player->colonies[0]->hasBuilding(Building::find(7));
        if($researchCenterLevel)
            $researchBonus *= pow(0.90, $researchCenterLevel);

        return $researchBonus;
    }
}
