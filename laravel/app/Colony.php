<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\Event;
use Illuminate\Support\Facades\DB;
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

    public function coordinates()
    {
        return $this->hasOne('App\Coordinate');
    }

    public function craftQueues()
    {
        return $this->belongsToMany('App\Unit','craft_queues','colony_id','unit_id')->withPivot('craft_end');
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
    public function getCraftingBonus()
    {
        $bonus = 1;

        $buildings = $this->buildings->filter(function ($value){
            return !is_null($value->crafting_bonus);
        });
        foreach($buildings as $building)
            $bonus *= pow($building->crafting_bonus, $building->pivot->level);

        $technologies = $this->player->technologies->filter(function ($value){
            return !is_null($value->crafting_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= pow($technology->crafting_bonus, $technology->pivot->level);

        return $bonus;
    }


    public function startCrafting(Unit $unit, int $qty)
    {
        $current = Carbon::now();

        $buildingTime = $unit->base_time;

        /** Application des bonus */
        $buildingTime *= $this->getCraftingBonus();

        $buildingPrices = $unit->getPrice($qty);
        foreach (config('stargate.resources') as $resource)
        {
            if($unit->$resource > 0)
                $this->$resource -= round($buildingPrices[$resource]);
        }

        if($this->craftQueues->count() > 0)
        {
            $lastQueue = $this->craftQueues->last();
            $lastQUeueCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->craft_end);
            if(!$lastQueue->isPast())
                $current = $lastQUeueCarbon;
        }

        for($cptQueue = 0; $cptQueue < $qty ; $cptQueue++ )
        {
            $buildingEnd = $current->addSeconds($buildingTime);
            $this->craftQueues()->attach([$unit->id => ['craft_end' => $buildingEnd]]);
        }

        $this->save();
        return $buildingEnd;
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

        if($this->player->notification)
        {
            try{
                $reminder = new Reminder;
                $reminder->reminder_date = Carbon::now()->addSecond($buildingTime);
                $reminder->reminder = "**Lvl ".$wantedLvl." - ".$building->name."** ".trans("reminder.isDone", [], $this->player->lang);
                $reminder->player_id = $this->player->id;
                $reminder->save();
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
            }
            //$this->player->reminders()->attach($reminder->id);
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

    public function checkCraftQueues()
    {
        try{
            echo PHP_EOL.'CHECK_CRAFTS_QUEUES';
            if($this->craftQueues->count() > 0)
            {
                $endedCrafts = $this->craftQueues->filter(function ($value){
                    return Carbon::createFromFormat("Y-m-d H:i:s",$value->pivot->craft_end)->isPast();
                });

                foreach($endedCrafts as $endedCraft)
                {
                    $unitExists = $this->units->filter(function ($value) use($endedCraft){               
                        return $value->id == $endedCraft->id;
                    });
                    if($unitExists->count() > 0)
                    {
                        $unitToUpdate = $unitExists->first();
                        $unitToUpdate->pivot->number++;
                        $unitToUpdate->pivot->save();
                    }
                    else
                    {
                        $this->units()->attach([$endedCraft->id => ['number' => 1]]);
                        $this->load('units'); 
                    }
                }
                DB::table('craft_queues')->where('craft_end', '<=', Carbon::now())->delete();
                $this->load('craftQueues'); 

            }
            else
            {
                echo PHP_EOL.'NO CRAFT';
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
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
            if($minuteToClaim >= 1)
            {
                foreach (config('stargate.resources') as $resource)
                {
                    $varNameProd = 'production_'.$resource;
                    $varNameStorage = 'storage_'.$resource;
                    $varNameConsumption = 'consumption_'.$resource;

                    $this->$resource += ($this->$varNameProd / 60) * $minuteToClaim;

                    if(!is_null($this->$varNameConsumption))
                        $this->$resource -= ($this->$varNameConsumption / 60) * $minuteToClaim;

                    if($this->$varNameStorage < $this->$resource)
                        $this->$resource = $this->$varNameStorage;
                }
                $this->military += ($this->production_military / 60) * $minuteToClaim;
                $this->e2pz += ($this->production_e2pz / 10080) * $minuteToClaim;

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
            $this->energy_max += floor($energyBuilding->getProductionEnergy($energyBuilding->pivot->level));
        
        $technologiesEnergyBonus = $this->player->technologies->filter(function ($value){
            return !is_null($value->energy_bonus);
        });
        /**Application bonus de production énergétique */
        $energyProductionBonus = 1;
        foreach($technologiesEnergyBonus as $technologyEnergyBonus)
            $energyProductionBonus *= pow($technologyEnergyBonus->energy_bonus, $technologyEnergyBonus->pivot->level);
        $this->energy_max *= $energyProductionBonus;

        $this->energy_used = 0;
        $this->consumption_naqahdah = 0;

        foreach($this->buildings as $building)
        {
            if($building->slug = 'naqadahreactor')
                $this->naqahdahConsumption += floor($building->getConsumption($building->pivot->level));
            else
                $this->energy_used += floor($building->getEnergy($building->pivot->level));
        }

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
            $this->production_military += $militaryBuilding->getProduction($militaryBuilding->pivot->level);
        }
        //+Bonus éventuels


        $e2pzBuildings = $this->buildings->filter(function ($value){
            return $value->production_type == 'e2pz' && $value->type == 'Science';
        });
        $this->production_e2pz = 0;
        foreach($e2pzBuildings as $e2pzBuilding)
        {
            if($this->production_e2pz == 0)
                $this->production_e2pz = config('stargate.base_prod.e2pz');
            $this->production_e2pz += $e2pzBuilding->getProductionE2PZ($e2pzBuilding->pivot->level);
        }
        //+Bonus éventuels
    }

    public function checkColony(){
        $this->checkProd();
        $this->player->checkTechnology();
        $this->checkBuilding();
        $this->checkCraftQueues();

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
