<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Boolean;
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

    public function artifacts()
    {
        return $this->hasMany('App\Artifact');
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

    public function shipQueues()
    {
        return $this->belongsToMany('App\Ship','ship_queues','colony_id','ship_id')->withPivot('ship_end');
    }

    public function units()
    {
        return $this->belongsToMany('App\Unit')->withPivot('number');
    }

    public function defenceQueues()
    {
        return $this->belongsToMany('App\Defence','defence_queues','colony_id','defence_id')->withPivot('defence_end');
    }

    public function defences()
    {
        return $this->belongsToMany('App\Defence')->withPivot('number');
    }

    public function ships()
    {
        return $this->belongsToMany('App\Ship')->withPivot('number');
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

    public function tradeCapacity()
    {
        $maxCapacity = 0;
        $transports = $this->units->filter(function ($value){
            return !is_null($value->capacity) && $value->capacity > 0;
        });
        foreach($transports as $transport)
            $maxCapacity += $transport->pivot->number * $transport->capacity;
        return $maxCapacity;
    }


    public function hasCraft(Unit $unit)
    {
        try{
            $unitExists = $this->units->filter(function ($value) use($unit){
                return $value->id == $unit->id;
            });
            if($unitExists->count() > 0)
            {
                $foundUnit = $unitExists->first();
                return $foundUnit->pivot->number;
            }
            else
                return false;
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return false;
        }
    }

    public function hasDefence(Defence $defence)
    {
        try{
            $defenceExists = $this->units->filter(function ($value) use($defence){
                return $value->id == $defence->id;
            });
            if($defenceExists->count() > 0)
            {
                $foundDefence = $defenceExists->first();
                return $foundDefence->pivot->number;
            }
            else
                return false;
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return false;
        }
    }

    public function hasShip(String $shipName)
    {
        try{
            $shipExists = $this->ships->filter(function ($value) use($shipName){
                return Str::startsWith($value->slug, $shipName);
            });
            if($shipExists->count() > 0)
                return $shipExists->first();
            else
                return false;
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return false;
        }
    }

    public function hasShipById(Int $shipId)
    {
        try{
            $shipExists = $this->ships->filter(function ($value) use($shipId){
                return Str::startsWith($value->id, $shipId);
            });
            if($shipExists->count() > 0)
                return $shipExists->first();
            else
                return false;
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return false;
        }
    }

    public function getResearchBonus($researchId = 0)
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
        {
            if($researchId != $technology->id)
                $bonus *= pow($technology->technology_bonus, $technology->pivot->level);
        }

        $buildingTimeBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Time' && $value->bonus_type == 'Research';
        });
        foreach($buildingTimeBonusList as $buildingTimeBonus)
        {
            $bonus *= $buildingTimeBonus->bonus_coef;
        }

        return $bonus;
    }

    public function getBuildingBonus($buildingId = 0)
    {
        $bonus = 1;

        $buildings = $this->buildings->filter(function ($value){
            return !is_null($value->building_bonus);
        });
        foreach($buildings as $building)
        {
            if($buildingId != $building->id)
                $bonus *= pow($building->building_bonus, $building->pivot->level);
        }

        $technologies = $this->player->technologies->filter(function ($value){
            return !is_null($value->building_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= pow($technology->building_bonus, $technology->pivot->level);

        $buildingTimeBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Time' && $value->bonus_type == 'Building';
        });
        foreach($buildingTimeBonusList as $buildingTimeBonus)
        {
            $bonus *= $buildingTimeBonus->bonus_coef;
        }

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

        $buildingTimeBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Time' && $value->bonus_type == 'Craft';
        });
        foreach($buildingTimeBonusList as $buildingTimeBonus)
        {
            $bonus *= $buildingTimeBonus->bonus_coef;
        }

        return $bonus;
    }

    public function getDefencebuildBonus()
    {
        $bonus = 1;

        $buildings = $this->buildings->filter(function ($value){
            return !is_null($value->defence_bonus);
        });
        foreach($buildings as $building)
            $bonus *= pow($building->defence_bonus, $building->pivot->level);

        $technologies = $this->player->technologies->filter(function ($value){
            return !is_null($value->defence_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= pow($technology->defence_bonus, $technology->pivot->level);

        $buildingTimeBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Time' && $value->bonus_type == 'Defence';
        });
        foreach($buildingTimeBonusList as $buildingTimeBonus)
        {
            $bonus *= $buildingTimeBonus->bonus_coef;
        }

        return $bonus;
    }

    public function getShipbuildBonus()
    {
        $bonus = 1;

        $buildings = $this->buildings->filter(function ($value){
            return !is_null($value->ship_bonus);
        });
        foreach($buildings as $building)
            $bonus *= pow($building->ship_bonus, $building->pivot->level);

        $technologies = $this->player->technologies->filter(function ($value){
            return !is_null($value->ship_bonus);
        });
        foreach($technologies as $technology)
            $bonus *= pow($technology->ship_bonus, $technology->pivot->level);

        $buildingTimeBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Time' && $value->bonus_type == 'Ship';
        });
        foreach($buildingTimeBonusList as $buildingTimeBonus)
        {
            $bonus *= $buildingTimeBonus->bonus_coef;
        }

        return $bonus;
    }


    public function startCrafting(Unit $unit, int $qty)
    {
        $current = Carbon::now();

        $buildingTime = $unit->base_time;

        /** Application des bonus */
        $buildingTime *= $this->getCraftingBonus();

        $coef = 1;
        $buildingPriceBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Price' && $value->bonus_type == 'Craft';
        });
        foreach($buildingPriceBonusList as $buildingPriceBonus)
        {
            $coef *= $buildingPriceBonus->bonus_coef;
        }

        $buildingPrices = $unit->getPrice($qty, $coef);
        foreach (config('stargate.resources') as $resource)
        {
            if($unit->$resource > 0)
                $this->$resource -= $buildingPrices[$resource];
            if($this->$resource < 0)
                $this->$resource = 0;
        }

        if($this->craftQueues->count() > 0)
        {
            $lastQueue = $this->craftQueues->last();
            $lastQUeueCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->craft_end);
            if(!$lastQUeueCarbon->isPast())
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

    public function startDefence(Defence $defence, int $qty)
    {
        $current = Carbon::now();

        $buildingTime = $defence->base_time;

        /** Application des bonus */
        $buildingTime *= $this->getDefencebuildBonus();

        $coef = 1;
        $buildingPriceBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Price' && $value->bonus_type == 'Defence';
        });
        foreach($buildingPriceBonusList as $buildingPriceBonus)
        {
            $coef *= $buildingPriceBonus->bonus_coef;
        }

        $buildingPrices = $defence->getPrice($qty, $coef);
        foreach (config('stargate.resources') as $resource)
        {
            if($defence->$resource > 0)
                $this->$resource -= $buildingPrices[$resource];
            if($this->resource < 0)
                $this->resource = 0;
        }

        if($this->defenceQueues->count() > 0)
        {
            $lastQueue = $this->defenceQueues->last();
            $lastQueueCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->defence_end);
            if(!$lastQueueCarbon->isPast())
                $current = $lastQueueCarbon;
        }

        for($cptQueue = 0; $cptQueue < $qty ; $cptQueue++ )
        {
            $buildingEnd = $current->addSeconds($buildingTime);
            $this->defenceQueues()->attach([$defence->id => ['defence_end' => $buildingEnd]]);
        }

        $this->save();
        return $buildingEnd;
    }


    public function startShip(Ship $ship, int $qty)
    {
        $current = Carbon::now();

        $buildingTime = $ship->base_time;

        /** Application des bonus */
        $buildingTime *= $this->getShipbuildBonus();

        $coef = 1;
        $buildingPriceBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Price' && $value->bonus_type == 'Ship';
        });
        foreach($buildingPriceBonusList as $buildingPriceBonus)
        {
            $coef *= $buildingPriceBonus->bonus_coef;
        }

        $buildingPrices = $ship->getPrice($qty, $coef);
        foreach (config('stargate.resources') as $resource)
        {
            if($ship->$resource > 0)
                $this->$resource -= $buildingPrices[$resource];
            if($this->resource < 0)
                $this->resource = 0;
        }

        if($this->shipQueues->count() > 0)
        {
            $lastQueue = $this->shipQueues->last();
            $lastQueueCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->ship_end);
            if(!$lastQueueCarbon->isPast())
                $current = $lastQueueCarbon;
        }

        for($cptQueue = 0; $cptQueue < $qty ; $cptQueue++ )
        {
            $buildingEnd = $current->addSeconds($buildingTime);
            $this->shipQueues()->attach([$ship->id => ['ship_end' => $buildingEnd]]);
        }

        $this->save();
        return $buildingEnd;
    }


    public function startBuilding(Building $building,Int $wantedLvl, Bool $removal)
    {
        $current = Carbon::now();

        //Temps de base
        $buildingTime = $building->getTime($wantedLvl);

        /** Application des bonus */
        $buildingTime *= $this->getBuildingBonus($building->id);

        if($removal)
        {
            $buildingTime /= 2;
            $this->active_building_remove = true;
        }
        else
        {
            $coef = 1;
            $buildingPriceBonusList = $this->artifacts->filter(function ($value){
                return $value->bonus_category == 'Price' && $value->bonus_type == 'Building';
            });
            foreach($buildingPriceBonusList as $buildingPriceBonus)
            {
                $coef *= $buildingPriceBonus->bonus_coef;
            }

            $buildingPrices = $building->getPrice($wantedLvl, $coef);
            foreach (config('stargate.resources') as $resource)
            {
                if($building->$resource > 0)
                    $this->$resource -= $buildingPrices[$resource];
                if($this->$resource < 0)
                    $this->$resource = 0;
            }
        }

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_building_id = $building->id;
        $this->active_building_end = $buildingEnd;

        if($this->player->notification)
        {
            try{
                $reminder = new Reminder;
                $reminder->reminder_date = Carbon::now()->addSecond($buildingTime);
                if($removal)
                    $reminder->reminder = trans("building.buildingRemoved", ['colony' => $this->name.' ['.$this->coordinates->humanCoordinates().']','name' => trans('building.'.$building->slug.'.name', [], $this->player->lang)], $this->player->lang);
                else
                    $reminder->reminder = $this->name." [".$this->coordinates->humanCoordinates()."] **Lvl ".$wantedLvl." - ".trans('building.'.$building->slug.'.name', [], $this->player->lang)."** ".trans("reminder.isDone", [], $this->player->lang);
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
                        $this->load('units');
                    }
                    else
                    {
                        $this->units()->attach([$endedCraft->id => ['number' => 1]]);
                        $this->load('units');
                    }
                }
                DB::table('craft_queues')->where([['craft_end', '<=', Carbon::now()],['colony_id',$this->id]])->delete();
                $this->load('craftQueues');

            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }

    public function checkDefenceQueues()
    {
        try{
            if($this->defenceQueues->count() > 0)
            {
                $endedDefences = $this->defenceQueues->filter(function ($value){
                    return Carbon::createFromFormat("Y-m-d H:i:s",$value->pivot->defence_end)->isPast();
                });

                foreach($endedDefences as $endedDefence)
                {
                    $defenceExists = $this->defences->filter(function ($value) use($endedDefence){
                        return $value->id == $endedDefence->id;
                    });
                    if($defenceExists->count() > 0)
                    {
                        $defenceToUpdate = $defenceExists->first();
                        $defenceToUpdate->pivot->number++;
                        $defenceToUpdate->pivot->save();
                        $this->load('defences');
                    }
                    else
                    {
                        $this->defences()->attach([$endedDefence->id => ['number' => 1]]);
                        $this->load('defences');
                    }
                }
                DB::table('defence_queues')->where([['defence_end', '<=', Carbon::now()],['colony_id',$this->id]])->delete();
                $this->load('defenceQueues');

            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }

    public function checkShipQueues()
    {
        try{
            if($this->shipQueues->count() > 0)
            {
                $endedShips = $this->shipQueues->filter(function ($value){
                    return Carbon::createFromFormat("Y-m-d H:i:s",$value->pivot->ship_end)->isPast();
                });

                foreach($endedShips as $endedShip)
                {
                    $shipExists = $this->ships->filter(function ($value) use($endedShip){
                        return $value->id == $endedShip->id;
                    });
                    if($shipExists->count() > 0)
                    {
                        $shipToUpdate = $shipExists->first();
                        $shipToUpdate->pivot->number++;
                        $shipToUpdate->pivot->save();
                        $this->load('ships');
                    }
                    else
                    {
                        $this->ships()->attach([$endedShip->id => ['number' => 1]]);
                        $this->load('ships');
                    }
                }
                DB::table('ship_queues')->where([['ship_end', '<=', Carbon::now()],['colony_id',$this->id]])->delete();
                $this->load('shipQueues');

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
        if(!is_null($this->last_claim))
        {
            $current = Carbon::now();
            $lastClaim = Carbon::createFromFormat("Y-m-d H:i:s",$this->last_claim);
            $minuteToClaim = $current->diffInMinutes($lastClaim);

            if($minuteToClaim > config('stargate.maxProdTime'))
                $minuteToClaim = config('stargate.maxProdTime');
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
                    elseif($this->$resource < 0)
                        $this->$resource = 0;
                }
                $this->military += ($this->production_military / 60) * $minuteToClaim;
                $this->E2PZ += ($this->production_e2pz / 10080) * $minuteToClaim;

                $this->last_claim = date("Y-m-d H:i:s");

                $this->save();
            }
        }
    }

    public function calcProd()
    {

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
            if($building->slug == 'naqahdahreactor')
                $this->consumption_naqahdah += floor($building->getConsumption($building->pivot->level));
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

            if(!is_null($this->player->premium_expiration))
                $this->$varName *= 1.25;
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

        $productionBonusList = $this->artifacts->filter(function ($value){
            return $value->bonus_category == 'Production';
        });
        foreach($productionBonusList as $productionBonus)
        {
            $varBonus = 'production_'.$productionBonus->bonus_resource;
            $this->$varBonus *= $productionBonus->bonus_coef;
        }
    }

    public function checkColony(){
        $this->checkProd();
        $this->player->checkTechnology();
        $this->checkBuilding();
        $this->checkCraftQueues();
        $this->checkDefenceQueues();
        $this->checkShipQueues();
    }

    public function buildingIsDone(Building $building)
    {
        try{
            $buildingExist = $this->buildings->filter(function ($value) use($building){
                return $value->id == $building->id;
            });
            if($this->active_building_remove)
            {
                $buildingToRemove = $buildingExist->first();
                if($buildingToRemove->pivot->level == 1)
                    $this->buildings()->detach($building->id);
                else
                {
                    $buildingToRemove->pivot->level = $buildingToRemove->pivot->level - 1;
                    $buildingToRemove->pivot->save();
                }
                $this->space_used--;
                $this->active_building_remove = false;

                $coef = 1;
                $buildingPriceBonusList = $this->artifacts->filter(function ($value){
                    return $value->bonus_category == 'Price' && $value->bonus_type == 'Building';
                });
                foreach($buildingPriceBonusList as $buildingPriceBonus)
                {
                    $coef *= $buildingPriceBonus->bonus_coef;
                }

                $wantedLvl = 1;
                $currentLvl = $this->player->activeColony->hasBuilding($building);
                if($currentLvl)
                    $wantedLvl = $currentLvl;

                $buildingPrices = $building->getPrice($wantedLvl, $coef);
                foreach (config('stargate.resources') as $resource)
                {
                    if($building->$resource > 0)
                    {
                        $newResource = $this->$resource + ceil($buildingPrices[$resource]*0.5);
                        if($this->{'storage_'.$resource} <= $newResource)
                            $newResource = $this->{'storage_'.$resource};
                        $this->$resource = $newResource;
                    }
                }
            }
            else
            {
                if($buildingExist->count() > 0)
                {
                    $buildingToUpgrade = $buildingExist->first();
                    $buildingToUpgrade->pivot->level++;
                    $buildingToUpgrade->pivot->save();
                }
                else
                {
                    $this->buildings()->attach([$building->id => ['level' => 1]]);
                    $this->load('buildings');
                }

                if($building->id == 20) //terraformeur
                    $this->space_max += 30;
                $this->space_used++;
            }
            $this->active_building_id = null;
            $this->active_building_end = null;
            $this->calcProd();
            $this->save();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    public function rndWeightedArtifact($values, $weights){
        $count = count($values);
        $i = 0;
        $n = 0;
        $randWeights = [];
        foreach($values as $value)
            $randWeights[] = $weights[$value];
        $num = mt_rand(0, array_sum($randWeights));
        while($i < $count){
            $n += $randWeights[$i];
            if($n >= $num){
                break;
            }
            $i++;
        }
        return $values[$i];
    }

    public function generateArtifact($options = [])
    {
        try{
            $categoryWeights = [
                'Production' => 30,
                'Time' => 20,
                'Price' => 20,
                'DefenceLure' => 10,
                'ColonyMax' => 10
            ];

            if(isset($options['bonusCategories']))
                $bonusCategories = $options['bonusCategories'];
            else
                $bonusCategories = ['Production', 'Time', 'Price', 'DefenceLure'];

            if(!isset($options['maxEnding']))
                $bonusCategories[] = 'ColonyMax';

            if(isset($options['bonusTypes']))
                $bonusTypes = $options['bonusTypes'];
            else
                $bonusTypes = ['Research', 'Building', 'Ship', 'Defence', 'Craft'];

            $bonusResources = ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'e2pz'];

            $newArtifact = new Artifact;
            $newArtifact->colony_id = $this->id;

            if(isset($options['forceBonus']))
                $isBonus = $options['forceBonus'];
            else
            {
                if(rand(0,100) > 75)
                    $isBonus = false;
                else
                    $isBonus = true;
            }

            $newArtifact->bonus_category = $this->rndWeightedArtifact($bonusCategories,$categoryWeights);

            if(in_array($newArtifact->bonus_category,['Price']))
            {
                $newArtifact->bonus_type = $bonusTypes[rand(0,count($bonusTypes)-1)];
                switch($newArtifact->bonus_type)
                {
                    case 'Research':
                    case 'Building':
                        $bonusCoef = rand(1,15)/100;
                    case 'Ship':
                        $bonusCoef = rand(1,10)/100;
                    break;
                    case 'Defence':
                    case 'Craft':
                        $bonusCoef = rand(1,20)/100;
                    break;
                    default:
                        $bonusCoef = rand(1,15)/100;
                    break;
                }
                if($isBonus)
                    $newArtifact->bonus_coef = 1-$bonusCoef;
                else
                    $newArtifact->bonus_coef = 1+$bonusCoef;
            }
            if(in_array($newArtifact->bonus_category,['Time']))
            {
                $newArtifact->bonus_type = $bonusTypes[rand(0,count($bonusTypes)-1)];
                $bonusCoef = rand(5,25)/100;
                if($isBonus)
                    $newArtifact->bonus_coef = 1-$bonusCoef;
                else
                    $newArtifact->bonus_coef = 1+$bonusCoef;
            }
            elseif(in_array($newArtifact->bonus_category,['DefenceLure']))
            {
                if($isBonus)
                    $newArtifact->bonus_coef = 2;
                else
                    $newArtifact->bonus_coef = 0.5;
            }
            elseif(in_array($newArtifact->bonus_category,['Production']))
            {
                $newArtifact->bonus_resource = $bonusResources[rand(0,count($bonusResources)-1)];
                $bonusCoef = rand(5,25)/100;
                if($isBonus)
                    $newArtifact->bonus_coef = 1+$bonusCoef;
                else
                    $newArtifact->bonus_coef = 1-$bonusCoef;
            }
            elseif(in_array($newArtifact->bonus_category,['ColonyMax']))
            {
                $alreadyOwned = Artifact::whereIn('colony_id',$this->player->colonies->pluck('id')->toArray())->where('bonus_category','Colony')->count();
                if($alreadyOwned > 0)
                    return $this->generateArtifact($options);
                $newArtifact->bonus_coef = 1;
                $maxEnding = null;
            }

            $minEnding = 1;
            if(isset($options['minEnding']))
                $minEnding = $options['minEnding'];
            if(isset($options['maxEnding']))
                $newArtifact->bonus_end = Carbon::now()->add(rand($minEnding,$options['maxEnding'])."h");

            $newArtifact->save();
            if($newArtifact->bonus_category == 'Production')
            {
                $this->refresh();
                $this->calcProd();
                $this->save();
            }
            return $newArtifact;
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }


}
