<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\Event;
use SebastianBergmann\CodeCoverage\Report\PHP;
use App\Utility\TopUpdater;

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

    public function units()
    {
        return $this->belongsToMany('App\Unit')->withPivot('number');
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

    
    public function getResearchBonus()
    {
        $bonus = 1;

        $buildings = $this->buildings->filter(function ($value){
            return !is_null($value->technology_bonus);
        });
        foreach($buildings as $building)
            $bonus *= pow($building->technology_bonus, $building->pivot->level);

        $technologies = $this->player->technologies->filter(function ($value){
            return !is_null($value->technology_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= pow($technology->technology_bonus, $technology->pivot->level);

        return $bonus;
    }
    public function getBuildingBonus()
    {
        $bonus = 1;

        $buildings = $this->buildings->filter(function ($value){
            return !is_null($value->building_bonus);
        });
        foreach($buildings as $building)
            $bonus *= pow($building->building_bonus, $building->pivot->level);

        $technologies = $this->player->technologies->filter(function ($value){
            return !is_null($value->building_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= pow($technology->building_bonus, $technology->pivot->level);

        return $bonus;
    }


    public function startBuilding(Building $building)
    {
        $current = Carbon::now();
        $wantedLvl = 1;
        $currentLevel = $this->hasBuilding($building);
        if($currentLevel)
            $wantedLvl += $currentLevel;

        //Temps de base
        $buildingTime = $building->getTime($wantedLvl);

        /** Application des bonus */
        $buildingTime *= $this->getBuildingBonus();

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_building_id = $building->id;
        $this->active_building_end = $buildingEnd;

        $buildingPrices = $building->getPrice($wantedLvl);
        foreach (config('stargate.resources') as $resource)
        {
            if($building->$resource > 0)
                $this->$resource -= round($buildingPrices[$resource]);
        }

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
                $this->buildingIsDone($this->activeBuilding);
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
                    $varNameProd = 'production_'.$resource;
                    $varNameStorage = 'storage_'.$resource;

                    $this->$resource += ($this->$varNameProd / 60) * $minuteToClaim;

                    if($this->$varNameStorage < $this->$resource)
                        $this->$resource = $this->$varNameStorage;
                }
                $this->clones += ($this->production_military / 60) * $minuteToClaim;

                $this->last_claim = date("Y-m-d H:i:s");

                $this->save();
            }
        }
    }

    public function calcProd()
    {
        echo PHP_EOL.'CALC_PROD'.PHP_EOL;

        $energyBuildings = $this->buildings->filter(function ($value){
            return $value->type == 'Energy';
        });
        $this->energy_max = 0;
        foreach($energyBuildings as $energyBuilding)
            $this->energy_max += floor($energyBuilding->getProduction($energyBuilding->pivot->level));
        
        $technologiesEnergyBonus = $this->player->technologies->filter(function ($value){
            return !is_null($value->energy_bonus);
        });
        /**Application bonus de production énergétique */
        $energyProductionBonus = 1;
        foreach($technologiesEnergyBonus as $technologyEnergyBonus)
            $energyProductionBonus *= pow($technologyEnergyBonus->energy_bonus, $technologyEnergyBonus->pivot->level);
        $this->energy_max *= $energyProductionBonus;

        $this->energy_used = 0;
        foreach($this->buildings as $building)
            $this->energy_used += floor($building->getEnergy($building->pivot->level));

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

        $storageBuildings = $this->buildings->filter(function ($value) use($resource){
            return $value->type == 'Storage';
        });
        foreach($storageBuildings as $storageBuilding)
        {
            $varName = 'storage_'.$storageBuilding->production_type;
            $this->$varName = 100000 * pow($storageBuilding->production_coefficient, $storageBuilding->pivot->level);
        }

        $militaryBuildings = $this->buildings->filter(function ($value){
            return $value->production_type == 'military' && $value->type == 'Military';
        });
        $this->production_military = 0;
        foreach($militaryBuildings as $militaryBuilding)
        {
            echo PHP_EOL.$militaryBuilding->getProduction($militaryBuilding->pivot->level);
            echo PHP_EOL.$militaryBuilding->name.' - '.$militaryBuilding->pivot->level;

            $this->production_military += $militaryBuilding->getProduction($militaryBuilding->pivot->level);
        }
        //+Bonus éventuels

        
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

        
        //$this->saveWithoutEvents();
        
    }

    public function checkColony(){
        $this->checkProd();
        $this->player->checkTechnology();
        $this->checkBuilding();
    }

    public function buildingIsDone(Building $building)
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
                $this->load('buildings'); // solution avec query
                //$this->refresh(); //solution complète
                //$this->buildings->push($comment); // Will manually add the new comment to the existing collection
            }

            $this->active_building_id = null;
            $this->active_building_end = null;
            $this->space_used++;
            $this->calcProd();
            $this->save();
            
            //$this->save();
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
