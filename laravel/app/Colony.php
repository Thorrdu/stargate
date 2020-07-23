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

    /*
    protected static function boot()
    {
        parent::boot();
        static::updating(function($colony) {
           // dd($colony);
           
        });

        static::updated(function($colony) {
            echo PHP_EOL.' COLONY EVENT UPDATED HORS OBSERVER';    
         });


        static::updating(function($colony) {
            // dd($colony);
            echo PHP_EOL.'COLONY OBERSER EVENT UPDATED';
            if(is_null($colony->active_building_id) && $colony->isDirty('active_building_id'))
            {
                echo PHP_EOL.'OBSRVER FORCE RECALC';
                $colony->unsetEventDispatcher();
                $colony->calcProd();
            }
         });

        static::creating(function($colony) {
            $colony->last_claim = Carbon::now()->format("Y-m-d H:i:s");
        });

        static::retrieved(function($colony) {
            echo PHP_EOL.' Retrieved hors observer';
            $colony->checkProd();
            $colony->player->checkTechnology();
            $colony->checkBuilding();
        });
    }*/

    public function saveWithoutEvents(array $options=[])
    {
        return static::withoutEvents(function() use ($options) {
            return $this->save($options);
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
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function getBuildingBonus()
    {
        $bonus = 1;

        /** Bonus Informatique et Communication -5% */
        $informationTechnology = $this->player->hasTechnology(Technology::find(1));
        if($informationTechnology)
            $bonus *= pow(0.95, $informationTechnology);

        /** Bonus Centre de recherche -10% */
        $researchCenterLevel = $this->hasBuilding(Building::find(6));
        if($researchCenterLevel)
            $bonus *= pow(0.90, $researchCenterLevel);

        return $bonus;
    }

    public function startBuilding(Building $building)
    {
        $current = Carbon::now();
        $levelWanted = 1;
        $currentLevel = $this->hasBuilding($building);
        if($currentLevel)
            $levelWanted += $currentLevel;

        //Temps de base
        $buildingTime = $building->getTime($levelWanted);

        /** Application des bonus */
        $buildingTime *= $this->getBuildingBonus();

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
                //$this->calcProd();
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
                foreach (config('stargate.resources') as $resource)
                {
                    $varName = 'production_'.$resource;
                    $this->$resource += round(($this->$varName / 60) * $minuteToClaim);
                }
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
                return $value->type == 'Energy';
            });
            $this->energy_max = 0;
            foreach($energyBuildings as $energyBuilding)
                $this->energy_max += round($energyBuilding->getProduction($energyBuilding->pivot->level));
            
            /** Bonus Energy +5% */
            $energyTechnology = $this->player->hasTechnology(Technology::find(4));
            if($energyTechnology)
                $this->energy_max *= pow(1.05, $energyTechnology);

            $this->energy_used = 0;
            foreach($this->buildings as $building)
                $this->energy_used += round($building->getEnergy($building->pivot->level));

            foreach (config('stargate.resources') as $resource)
            {
                $productionBuildings = $this->buildings->filter(function ($value) use($resource){
                    return $value->production_type == $resource && $value->type == 'Production';
                });
                $varName = 'production_'.$resource;
                $this->$varName = config('stargate.base_prod.'.$resource);
                foreach($productionBuildings as $productionBuilding)
                    $this->$varName += $productionBuilding->getProduction($productionBuilding->pivot->level);
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

            
            $this->saveWithoutEvents();
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
