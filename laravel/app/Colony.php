<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\Event;
use SebastianBergmann\CodeCoverage\Report\PHP;

class Colony extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_claim' => 'datetime',
    ];

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
        if(!$currentLevel)
            $currentLevel = 0;

        $buildingTime = $building->time_base;
        if($currentLevel > 0)    
            $buildingTime = $building->time_base * pow($building->time_coefficient, $currentLevel);

        $currentRobotic = $this->player->colonies[0]->hasBuilding(Building::find(6));
        if($currentRobotic)
            $buildingTime *= pow(0.9, $currentRobotic);

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_building_id = $building->id;
        $this->active_building_end = $buildingEnd;
        $this->save();
        return $this->active_building_end;
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

                $goldProduced = round(($this->production_gold / 60) * $minuteToClaim);
                $this->gold += $goldProduced;

                $quartzProduced = round(($this->production_quartz / 60) * $minuteToClaim);
                $this->quartz += $quartzProduced;

                $naqahdahProduced = round(($this->production_naqahdah / 60) * $minuteToClaim);
                $this->naqahdah += $naqahdahProduced;

                $this->last_claim = date("Y-m-d H:i:s");

                $this->save();
            }
        }
    }

    public function calcProd()
    {
        echo PHP_EOL.'CALC_PROD'.PHP_EOL;
        if(!is_null($this->last_claim))
        {
            $energyBuildings = $this->buildings->filter(function ($value){
                //echo $value->name.' - Type '. $value->production_type .' - Level '. $value->pivot->level.PHP_EOL;
                return $value->type == 'Energy';// || $value->type == 'Energy'
            });
            $newEnergy = 0;
            foreach($energyBuildings as $energyBuilding)
            {
                $newEnergy += $energyBuilding->production_base * pow($energyBuilding->production_coefficient, $energyBuilding->pivot->level);
            }
            $this->energy_max = $newEnergy;
            
            $energyUsed = 0;
            foreach($this->buildings as $building)
            {
                $energyUsed += $building->energy_base * pow($building->energy_coefficient, $building->pivot->level);
            }
            $this->energy_used = $energyUsed;

            foreach (config('stargate.resources') as $resource)
            {
                $productionBuildings = $this->buildings->filter(function ($value) use($resource){
                    //echo $value->name.' - Type '. $value->production_type .' - Level '. $value->pivot->level.PHP_EOL;
                    return $value->production_type == $resource;// || $value->type == 'Energy'
                });
                $varName = 'production_'.$resource;

                $this->$varName = config('stargate.base_prod.'.$resource);
                foreach($productionBuildings as $productionBuilding)
                {
                    $this->$varName += round($productionBuilding->production_base * pow($productionBuilding->production_coefficient, $productionBuilding->pivot->level));
                }
                //+Bonus éventuels
            }


            

            //User::find(1)->roles()->updateExistingPivot($roleId, $attributes);
            /*$ironProdBuildings = $this->buildings->filter(function ($value) {
                echo $value->name.' - Type '. $value->production_type .' - Level '. $value->pivot->level.PHP_EOL;
                return $value->production_type == 'iron';// || $value->type == 'Energy'
            });*/
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

            
            $this->save();
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
                $this->buildings()->attach([$building->id => ['level' => 1]]);
            }

            $this->active_building_id = null;
            $this->active_building_end = null;
            $this->save();
            $this->calcProd();
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
