<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\Event;
use SebastianBergmann\CodeCoverage\Report\PHP;

class Colony extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::updating(function($colony) {
           // dd($colony);
        });

        static::creating(function($colony) {
            $colony->last_claim = Carbon::now()->format("Y-m-d H:i:s");
        });

        static::retrieved(function($colony) {

            $colony->checkProd();
            $colony->checkBuilding();

        });
    }

    public function player(){
        return $this->belongsTo('App\Player');
    }
    public function buildings()
    {
        return $this->belongsToMany('App\Building')->withPivot('level');
    }
    public function activeBuilding()
    {
        return $this->hasOne('App\Building','id','active_building_id');
    }

    public function hasBuilding(Building $building)
    {
        try{
            $buildingExist = $this->buildings->filter(function ($value) use($building){
                return $value->id == $building->id;
            });
            if($buildingExist->count() > 0)
            {
                $foundBuilding = $buildingExist->first();
                return $foundBuilding->pivot->level;
            }
            else
                return false;
            //$buildingExist = ColonyBuilding::where(['colony_id' => $this->id, 'building_id' => $building->id])->first();
            //return $buildingExist->level;
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function startBuilding(Building $building)
    {
        $current = Carbon::now();
        $currentLevel = $this->hasBuilding($building);
        if($currentLevel)
            $levelMultiplier = $currentLevel + 1;
        else
            $levelMultiplier = 1;

        $this->active_building_id = $building->id;
        $buildingTime = $building->time_base * ($building->time_coefficient * $levelMultiplier);
        $buildingEnd = $current->addSeconds($buildingTime);
        $this->active_building_end = $buildingEnd;
        $this->save();
    }

    public function checkBuilding()
    {
        echo PHP_EOL.'CHECK_BUILDING';
        if(!is_null($this->active_building_end))
        {
            $endingDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->active_building_end);
            if($endingDate->isPast())
            {
                $this->builingdIsDone($this->activeBuilding);
            }
        }
    }

    public function checkProd()
    {
        $this->calcProd();
        echo PHP_EOL.'CHECK_PROD'.PHP_EOL;
        if(!is_null($this->last_claim))
        {
            $current = Carbon::now();
            $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->last_claim);
            $minuteToClaim = $current->diffInMinutes($lastClaim);
            if($minuteToClaim >= 5)
            {
                $ironProduced = round(($this->production_iron / 60) * $minuteToClaim);
                $this->iron += $ironProduced;
                $this->save();

                $goldProduced = round(($this->production_gold / 60) * $minuteToClaim);
                $this->gold += $goldProduced;
                $this->save();

                $quartzProduced = round(($this->production_quartz / 60) * $minuteToClaim);
                $this->quartz += $quartzProduced;
                $this->save();

                $naqahdahProduced = round(($this->production_naqahdah / 60) * $minuteToClaim);
                $this->naqahdah += $naqahdahProduced;
                $this->save();
            }
        }
    }

    public function calcProd()
    {
        echo PHP_EOL.'CALC_PROD'.PHP_EOL;
        if(!is_null($this->last_claim))
        {
            /*BASE
            Iron :	Gold :	Quartz :	Naqadah :
            20	10	5	2
            */
            $current = Carbon::now();
            $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->last_claim);
            $minuteToClaim = $current->diffInMinutes($lastClaim);
            if($minuteToClaim >= 5)
            {
                //User::find(1)->roles()->updateExistingPivot($roleId, $attributes);
                $ironProdBuildings = $this->buildings->filter(function ($value) {
                    echo $value->name.' - Type '. $value->type .' - Level '. $value->pivot->level.PHP_EOL;
                    return $value->type == 'Production' && $value->production_type == 'Iron';// || $value->type == 'Energy'
                });
                /*
                $ironProdBuildings = Building::orderBy('building.production_type', 'asc')
                                                ->with('colony')
                                                //->buildings()
                                                //->join("buildings","buildings.id","=","building_id")
                                                ->where(['colony_id' => $this->id, 'building.type' => 'Production'])
                                                ->where(['building.production_type' => 'Iron'])
                                                ->dump()
                                                ->get();
                */
                $ironProdPerHour = 20;
                foreach($ironProdBuildings as $ironProdBuilding)
                {
                    $ironProdPerHour += $ironProdBuilding->production_base * ($ironProdBuilding->production_coefficient * $ironProdBuilding->pivot->level);
                }
                //+Bonus éventuels
                $this->production_iron = $ironProdPerHour;
            }
        }
    }

    public function builingdIsDone(Building $building)
    {
        try{

            $buildingExist = $this->buildings->filter(function ($value) use($building){               
                return $value->id == $building->id;
            });
            if($buildingExist->count() > 0)
            {
                $buildingToUpgrade = $buildingExist->first();
                $buildingToUpgrade->pivot->level++;
                $buildingToUpgrade->pivot->save();
            }
            else
            {
                $this->buildings->attach($building);
            }

            $this->active_building_id = null;
            $this->active_building_end = null;
            $this->calcProd();
            $this->save();

            /*
            $buildingExist = ColonyBuilding::where(['colony_id' => $this->id, 'building_id' => $building->id])->first();
            if($buildingExist)
            {
                $buildingExist->level = $buildingExist->level + 1;
                $buildingExist->save();
            }
            else
            {
                $newBuilding = new ColonyBuilding();
                $newBuilding->colony_id = $this->id;
                $newBuilding->building_id = $building->id;
                $newBuilding->save();
                $this->buildings->push($newBuilding);
            }
            $this->active_building_id = null;
            $this->active_building_end = null;
            $this->calcProd();
            $this->save();
            */
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

}
