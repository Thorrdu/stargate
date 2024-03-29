<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\Event;
use App\Utility\PlayerUtility;
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
        return $this->belongsToMany('App\Building')->withPivot('level')->orderBy('buildings.id','ASC');
    }

    public function artifacts()
    {
        return $this->hasMany('App\Artifact');
    }

    public function activeBuilding()
    {
        return $this->hasOne('App\Building','id','active_building_id');
    }

    public function buildingQueue()
    {
        return $this->belongsToMany('App\Building','colony_buildings_queue','colony_id','building_id')->withPivot('level');
    }

    public function coordinates()
    {
        return $this->hasOne('App\Coordinate','id','coordinate_id');
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
        return $this->belongsToMany('App\Ship','colony_ship','colony_id','ship_id')->withPivot('number');
    }

    public function reyclingQueue()
    {
        return $this->belongsToMany('App\Ship','colony_reycling_queue','colony_id','ship_id')->withPivot('ship_end');
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
            return !is_null($value->capacity) && $value->type == 'Transport';
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return false;
        }
    }

    public function hasDefence(Defence $defence)
    {
        try{
            $defenceExists = $this->defences->filter(function ($value) use($defence){
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return false;
        }
    }

    public function hasShip(Ship $ship)
    {
        try{
            $shipExists = $this->ships->filter(function ($value) use($ship){
                return $value->id == $ship->id;
            });
            if($shipExists->count() > 0)
            {
                $foundShip = $shipExists->first();
                return $foundShip->pivot->number;
            }
            else
                return false;
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return false;
        }
    }

    public function hasUnitById(Int $unitId)
    {
        try{
            $unitExists = $this->units->filter(function ($value) use($unitId){
                return Str::startsWith($value->id, $unitId);
            });
            if($unitExists->count() > 0)
                return $unitExists->first();
            else
                return false;
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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

        $bonus *= $this->getArtifactBonus(['bonus_category' => 'Time', 'bonus_type' => 'Research']);

        if(!is_null($this->player->premium_expiration))
            $bonus *= config('stargate.premium.bonusTime');

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

        $bonus *= $this->getArtifactBonus(['bonus_category' => 'Time', 'bonus_type' => 'Building']);

        if(!is_null($this->player->premium_expiration))
            $bonus *= config('stargate.premium.bonusTime');

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

        $bonus *= $this->getArtifactBonus(['bonus_category' => 'Time', 'bonus_type' => 'Craft']);

        if(!is_null($this->player->premium_expiration))
            $bonus *= config('stargate.premium.bonusTime');

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

        $bonus *= $this->getArtifactBonus(['bonus_category' => 'Time', 'bonus_type' => 'Defence']);

        if(!is_null($this->player->premium_expiration))
            $bonus *= config('stargate.premium.bonusTime');

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

        $bonus *= $this->getArtifactBonus(['bonus_category' => 'Time', 'bonus_type' => 'Ship']);

        if(!is_null($this->player->premium_expiration))
            $bonus *= config('stargate.premium.bonusTime');

        return $bonus;
    }


    public function startCrafting(Unit $unit, int $qty)
    {
        $current = Carbon::now();

        $buildingTime = $unit->base_time;

        /** Application des bonus */
        $buildingTime *= $this->getCraftingBonus();

        $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Craft']);
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

        $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Defence']);
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

        $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Ship']);
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

    public function startRecyclingShip(Ship $ship, int $qty)
    {
        $current = Carbon::now();

        $buildingTime = $ship->base_time;

        /** Application des bonus */
        $buildingTime *= $this->getShipbuildBonus();
        $buildingTime /= 4;

        if($this->reyclingQueue->count() > 0)
        {
            $lastQueue = $this->reyclingQueue->last();
            $lastQueueCarbon = Carbon::createFromFormat("Y-m-d H:i:s",$lastQueue->pivot->ship_end);
            if(!$lastQueueCarbon->isPast())
                $current = $lastQueueCarbon;
        }

        for($cptQueue = 1; $cptQueue <= $qty ; $cptQueue++ )
        {
            $buildingEnd = $current->addSeconds($buildingTime);
            $this->reyclingQueue()->attach([$ship->id => ['ship_end' => $buildingEnd]]);
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
            $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);
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

                if($this->player->notification && $this->craftQueues->count() == 0)
                {
                    $colonyArr = array_filter(
                        $this->player->colonies->toArray(),
                        function ($colony) {
                            return $colony['id'] == $this->id;
                        }
                    );
                    $colonyNumber = trans('generic.colony', [], $this->player->lang).' n° '.(key($colonyArr)+1).':';

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    $reminder->reminder = trans('colony.craftQueueEnded', ['colony' => $colonyNumber.' '.$this->name." [".$this->coordinates->humanCoordinates()."] "], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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

                if($this->player->notification && $this->defenceQueues->count() == 0)
                {
                    $colonyArr = array_filter(
                        $this->player->colonies->toArray(),
                        function ($colony) {
                            return $colony['id'] == $this->id;
                        }
                    );
                    $colonyNumber = trans('generic.colony', [], $this->player->lang).' n° '.(key($colonyArr)+1).':';

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    $reminder->reminder = trans('colony.defenceQueueEnded', ['colony' => $colonyNumber.' '.$this->name." [".$this->coordinates->humanCoordinates()."] "], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function checkShipRecyclingQueues()
    {
        try{
            if($this->reyclingQueue->count() > 0)
            {
                $endedShips = $this->reyclingQueue->filter(function ($value){
                    return Carbon::createFromFormat("Y-m-d H:i:s",$value->pivot->ship_end)->isPast();
                });

                foreach($endedShips as $endedShip)
                {
                    $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Ship']);
                    $buildingPrices = $endedShip->getPrice(1,$coef);
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($endedShip->$resource > 0)
                        {
                            $newResource = $this->$resource + ceil($buildingPrices[$resource]*0.80);
                            if($this->{'storage_'.$resource} <= $newResource)
                                $newResource = $this->{'storage_'.$resource};
                            $this->$resource = $newResource;
                        }
                    }
                }
                $this->save();
                DB::table('colony_reycling_queue')->where([['ship_end', '<=', Carbon::now()],['colony_id',$this->id]])->delete();
                $this->load('reyclingQueue');

                if($this->player->notification && $this->reyclingQueue->count() == 0)
                {
                    $colonyArr = array_filter(
                        $this->player->colonies->toArray(),
                        function ($colony) {
                            return $colony['id'] == $this->id;
                        }
                    );
                    $colonyNumber = trans('generic.colony', [], $this->player->lang).' n° '.(key($colonyArr)+1).':';

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    $reminder->reminder = trans('colony.recyclingQueueEnded', ['colony' => $colonyNumber.' '.$this->name." [".$this->coordinates->humanCoordinates()."] "], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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

                if($this->player->notification && $this->shipQueues->count() == 0)
                {
                    $colonyArr = array_filter(
                        $this->player->colonies->toArray(),
                        function ($colony) {
                            return $colony['id'] == $this->id;
                        }
                    );
                    $colonyNumber = trans('generic.colony', [], $this->player->lang).' n° '.(key($colonyArr)+1).':';

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    $reminder->reminder = trans('colony.shipQueueEnded', ['colony' => $colonyNumber.' '.$this->name." [".$this->coordinates->humanCoordinates()."] "], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
            if($minuteToClaim >= 5)
            {
                foreach (config('stargate.resources') as $resource)
                {
                    $varNameProd = 'production_'.$resource;
                    $varNameStorage = 'storage_'.$resource;
                    $varNameConsumption = 'consumption_'.$resource;

                    if(!is_null($this->$varNameConsumption))
                            $this->$resource -= ($this->$varNameConsumption / 60) * $minuteToClaim;

                    $producedResources = ($this->$varNameProd / 60) * $minuteToClaim;
                    if(($this->$resource + $producedResources) <= $this->$varNameStorage)
                        $this->$resource += $producedResources;
                    elseif($this->$resource < $this->$varNameStorage && ($this->$resource + $producedResources) > $this->$varNameStorage)
                        $this->$resource = $this->$varNameStorage;

                    if(($this->$varNameStorage*1.25) < $this->$resource)
                        $this->$resource = $this->$varNameStorage*1.25;
                    elseif($this->$resource < 0)
                        $this->$resource = 0;
                }
                $this->military += ($this->production_military / 60) * $minuteToClaim;
                $this->E2PZ += ($this->production_e2pz / 10080) * $minuteToClaim;
                if($this->E2PZ < 0)
                    $this->E2PZ = 0;

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
            if($building->id == 10 /*Reacteur au Naqahdah*/)
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
                $this->$varName *= config('stargate.premium.bonusProduction');

            $this->$varName *= $this->getArtifactBonus(['bonus_category' => 'Production', 'bonus_resource' => $resource]);
        }

        $storageBuildings = $this->buildings->filter(function ($value){
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
        $this->production_military *= $this->getArtifactBonus(['bonus_category' => 'Production', 'bonus_resource' => 'military']);
        if(!is_null($this->player->premium_expiration))
            $this->production_military *= config('stargate.premium.bonusProduction');

        $e2pzBuildings = $this->buildings->filter(function ($value){
            return $value->production_type == 'e2pz' && $value->type == 'Production';
        });
        $this->production_e2pz = 0;
        foreach($e2pzBuildings as $e2pzBuilding)
        {
            if($this->production_e2pz == 0)
                $this->production_e2pz = config('stargate.base_prod.e2pz');
            $this->production_e2pz += $e2pzBuilding->getProductionE2PZ($e2pzBuilding->pivot->level);
        }
        $this->production_e2pz *= $this->getArtifactBonus(['bonus_category' => 'Production', 'bonus_resource' => 'e2pz']);
        if(!is_null($this->player->premium_expiration))
            $this->production_e2pz *= config('stargate.premium.bonusProduction');
    }

    public function checkColony(){
        $this->checkShipRecyclingQueues();
        $this->checkProd();
        $this->player->checkTechnology();
        $this->checkBuilding();
        $this->checkCraftQueues();
        $this->checkDefenceQueues();
        $this->checkShipQueues();
    }

    public function getArtifactBonus(Array $options)
    {
        $returnBonus = 1;
        switch(count($options))
        {
            case 1:
                $bonusList = $this->artifacts->filter(function ($value) use($options){
                    $key1 = array_keys($options)[0];
                    return $value->$key1 == $options[$key1];
                });
            break;
            case 2:
                $bonusList = $this->artifacts->filter(function ($value) use($options){
                    $key1 = array_keys($options)[0];
                    $key2 = array_keys($options)[1];
                    return $value->$key1 == $options[$key1] && $value->$key2 == $options[$key2];
                });
            break;
            default:
                return 1;
            break;
        }
        foreach($bonusList as $bonus)
            $returnBonus *= $bonus->bonus_coef;

        /*
        $table->enum('bonus_category', ['Production', 'Time', 'Price', 'DefenceLure', 'ColonyMax']);
        $table->enum('bonus_type', ['Research', 'Building', 'Ship', 'Defence', 'Craft'])->nullable();
        $table->enum('bonus_resource', ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'e2pz'])->nullable();
        */
        if($returnBonus > 1.25 && in_array($options['bonus_category'],array('Production','Time','Price')))
            $returnBonus = 1.25;
        elseif($returnBonus < 0.75 && in_array($options['bonus_category'],array('Production','Time','Price')))
            $returnBonus = 0.75;

        return $returnBonus;
    }

    public function buildingIsDone(Building $building)
    {
        try{
            $removal = false;
            $newLvl = 1;
            $buildingExist = $this->buildings->filter(function ($value) use($building){
                return $value->id == $building->id;
            });
            if($this->active_building_remove && $buildingExist->count() == 0)
            {
                $this->active_building_remove = false;
                $this->save();
                return;
            }
            elseif($this->active_building_remove)
            {
                $removal = true;
                $buildingToRemove = $buildingExist->first();
                if($buildingToRemove->pivot->level == 1)
                    $this->buildings()->detach($building->id);
                else
                {
                    $buildingToRemove->pivot->level = $buildingToRemove->pivot->level - 1;
                    $buildingToRemove->pivot->save();
                }
                $this->space_used--;

                $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);

                $wantedLvl = 1;
                $currentLvl = $this->hasBuilding($building);
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
                    $newLvl = $buildingToUpgrade->pivot->level;
                }
                else
                {
                    $this->buildings()->attach([$building->id => ['level' => 1]]);
                }
                $this->load('buildings');

                if($building->id == 20) //terraformeur
                    $this->space_max += 30;
                $this->space_used++;
            }
            $this->active_building_remove = false;
            $this->active_building_id = null;
            $this->active_building_end = null;
            $this->calcProd();
            $this->checkProd();
            $this->save();

            if(!is_null($this->player->premium_expiration))
                $this->checkBuildingQueue();

            if($this->player->notification)
            {
                $colonyArr = array_filter(
                    $this->player->colonies->toArray(),
                    function ($colony) {
                        return $colony['id'] == $this->id;
                    }
                );
                $colonyNumber = trans('generic.colony', [], $this->player->lang).' n° '.(key($colonyArr)+1).':';

                try{
                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    if($removal)
                        $reminder->reminder = trans("building.buildingRemoved", ['colony' => $colonyNumber.' '.$this->name.' ['.$this->coordinates->humanCoordinates().']','name' => trans('building.'.$building->slug.'.name', [], $this->player->lang)], $this->player->lang);
                    else
                        $reminder->reminder = $colonyNumber.' '.$this->name." [".$this->coordinates->humanCoordinates()."] **Lvl ".$newLvl." - ".trans('building.'.$building->slug.'.name', [], $this->player->lang)."** ".trans("reminder.isDone", [], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
                catch(\Exception $e)
                {
                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                }
            }

        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function checkBuildingQueue()
    {
        try{
            if($this->buildingQueue->count() > 0)
            {
                $buildingToBuild = $this->buildingQueue[0];

                $wantedLvl = 1;
                $currentLvl = $this->hasBuilding($buildingToBuild);
                if($currentLvl)
                    $wantedLvl += $currentLvl;

                $canceledReason = '';
                if(!is_null($buildingToBuild->level_max) && $wantedLvl > $buildingToBuild->level_max)
                    $canceledReason = trans('building.buildingMaxed', [], $this->player->lang);
                elseif(($this->space_max - $this->space_used) <= 0 && $buildingToBuild->id != 20)
                    $canceledReason = trans('building.missingSpace', [], $this->player->lang);
                else
                {

                    //Requirement
                    $hasRequirements = true;
                    foreach($buildingToBuild->requiredTechnologies as $requiredTechnology)
                    {
                        $currentLvl = $this->player->hasTechnology($requiredTechnology);
                        if(!($currentLvl && $currentLvl >= $requiredTechnology->pivot->level))
                            $hasRequirements = false;
                    }
                    foreach($buildingToBuild->requiredBuildings as $requiredBuilding)
                    {
                        $currentLvl = $this->hasBuilding($requiredBuilding);
                        if(!($currentLvl && $currentLvl >= $requiredBuilding->pivot->level))
                            $hasRequirements = false;
                    }
                    if(!$hasRequirements)
                        $canceledReason = trans('generic.missingRequirements', [], $this->player->lang);

                    $hasEnough = true;
                    $coef = $this->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Building']);

                    $buildingPrices = $buildingToBuild->getPrice($wantedLvl, $coef);
                    $missingResString = "";
                    foreach (config('stargate.resources') as $resource)
                    {
                        if($buildingToBuild->$resource > 0 && $buildingPrices[$resource] > $this->$resource)
                        {
                            $hasEnough = false;
                            $missingResString .= " ".config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format(ceil($buildingPrices[$resource]-$this->$resource));
                        }
                    }
                    if(!$hasEnough)
                        $canceledReason = trans('generic.notEnoughResources', ['missingResources' => $missingResString], $this->player->lang);
                    else
                    {
                        if($buildingToBuild->energy_base > 0 && $buildingToBuild->id != 10 /*Reacteur au Naqahdah*/)
                        {
                            $energyPrice = $buildingToBuild->getEnergy($wantedLvl);
                            if($wantedLvl > 1)
                                $energyPrice -= $buildingToBuild->getEnergy($wantedLvl-1);
                            $energyLeft = ($this->energy_max - $this->energy_used);
                            $missingEnergy = number_format($energyPrice - $energyLeft);
                            if($missingEnergy > 0)
                                $canceledReason = trans('building.notEnoughEnergy', ['missingEnergy' => $missingEnergy], $this->player->lang);
                        }

                        if(!is_null($this->player->active_technology_id) && $buildingToBuild->id == 7 && $this->id == $this->player->active_technology_colony_id)
                            $canceledReason = trans('generic.busyBuilding', [], $this->player->lang);
                        elseif( $this->defenceQueues->count() > 0 && $buildingToBuild->id == 15 )
                            $canceledReason = trans('generic.busyBuilding', [], $this->player->lang);
                        elseif( $this->craftQueues->count() > 0 && $buildingToBuild->id == 9 )
                            $canceledReason = trans('generic.busyBuilding', [], $this->player->lang);
                        elseif(empty($canceledReason))
                        {
                            $this->startBuilding($buildingToBuild,$wantedLvl,false);

                            DB::table('colony_buildings_queue')
                            ->where([['colony_id', $this->id], ['building_id', $buildingToBuild->id], ['level', $buildingToBuild->pivot->level]])
                            ->delete();

                            $this->load('buildingQueue');
                        }
                    }
                }

                if(!empty($canceledReason))
                {
                    $this->buildingQueue()->detach();

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    $reminder->reminder = trans("building.queueCanceled", [
                        'colony' => $this->name.' ['.$this->coordinates->humanCoordinates().']',
                        'buildingName' => trans('building.'.$buildingToBuild->slug.'.name', [], $this->player->lang),
                        'reason'=> $canceledReason
                    ], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
                elseif($this->player->notification && $this->buildingQueue->count() == 0)
                {
                    $colonyArr = array_filter(
                        $this->player->colonies->toArray(),
                        function ($colony) {
                            return $colony['id'] == $this->id;
                        }
                    );
                    $colonyNumber = trans('generic.colony', [], $this->player->lang).' n° '.(key($colonyArr)+1).':';

                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->addSecond(1);
                    $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                    $reminder->reminder = trans('colony.buildingQueueEnded', ['colony' => $colonyNumber.' '.$this->name." [".$this->coordinates->humanCoordinates()."] "], $this->player->lang);
                    $reminder->player_id = $this->player->id;
                    $reminder->save();
                }
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function generateArtifact($options = [])
    {
        if(isset($options['source']))
            $source = $options['source'];
        else
            $source = 'any';
        $forceMax = false;

        try{
            if($source == 'vote')
            {
                $forceMax = true;
                $categoryWeights = [
                    'Time' => 10,
                    'Production' => 15,
                    'DefenceLure' => 5,
                    'Price' => 15,
                ];
            }
            else
            {
                $categoryWeights = [
                    'Time' => 20,
                    'Production' => 15,
                    'maxSpace' => 15,
                    'ColonyMax' => 10,
                    'DefenceLure' => 5,
                    'Price' => 3,
                ];
            }

            if(isset($options['bonusCategories']))
                $bonusCategories = $options['bonusCategories'];
            else
                $bonusCategories = ['Production', 'Time', 'Price', 'DefenceLure'];

            if(!isset($options['maxEnding']))
            {
                $bonusCategories[] = 'ColonyMax';
                $bonusCategories[] = 'maxSpace';
            }

            if(isset($options['bonusTypes']))
                $bonusTypes = $options['bonusTypes'];
            else
                $bonusTypes = ['Research', 'Building', 'Ship', 'Defence', 'Craft'];

            $bonusResources = ['iron', 'gold', 'quartz', 'naqahdah', 'military', 'e2pz'];
            if(!$this->prime_colony)
                $bonusResources = ['iron', 'gold', 'quartz', 'naqahdah', 'military'];

            $newArtifact = new Artifact;
            $newArtifact->colony_id = $this->id;

            if(isset($options['forceBonus']))
                $isBonus = $options['forceBonus'];
            else
            {
                if(rand(0,100) >= 96)
                    $isBonus = false;
                else
                    $isBonus = true;
            }

            $iaTech = Technology::find(5);
            $iaTechLvl = $this->player->hasTechnology($iaTech);
            if(!$iaTechLvl)
                $iaTechLvl = 0;
            $iaTechBonus = floor($iaTechLvl/4);
            $maxBonus = 5 + $iaTechBonus;
            $minBonus = 2 + $iaTechBonus;

            if($forceMax)
                $maxBonus += 2;

            $newArtifact->bonus_category = PlayerUtility::rngWeighted($bonusCategories,$categoryWeights);

            if(in_array($newArtifact->bonus_category,['Price']))
            {
                $newArtifact->bonus_type = $bonusTypes[rand(0,count($bonusTypes)-1)];
                switch($newArtifact->bonus_type)
                {
                    case 'Research':
                    case 'Building':
                        $bonusCoef = rand($minBonus,$maxBonus)/100;
                    case 'Ship':
                        $bonusCoef = rand($minBonus,$maxBonus)/100;
                    break;
                    case 'Defence':
                    case 'Craft':
                        $bonusCoef = rand($minBonus,$maxBonus)/100;
                    break;
                    default:
                        $bonusCoef = rand($minBonus,$maxBonus)/100;
                    break;
                }
                if($forceMax)
                    $bonusCoef = $maxBonus/100;

                if($isBonus)
                    $newArtifact->bonus_coef = 1-$bonusCoef;
                else
                    $newArtifact->bonus_coef = 1+$bonusCoef;
            }
            if(in_array($newArtifact->bonus_category,['Time']))
            {
                $newArtifact->bonus_type = $bonusTypes[rand(0,count($bonusTypes)-1)];
                $bonusCoef = rand($minBonus,$maxBonus)/100;
                if($forceMax)
                    $bonusCoef = $maxBonus/100;

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
                $bonusCoef = rand($minBonus,$maxBonus)/100;
                if($forceMax)
                    $bonusCoef = $maxBonus/100;
                if($isBonus)
                    $newArtifact->bonus_coef = 1+$bonusCoef;
                else
                    $newArtifact->bonus_coef = 1-$bonusCoef;
            }
            elseif(in_array($newArtifact->bonus_category,['maxSpace']))
            {
                $additionalSpace = rand((20+$iaTechBonus),(45+$iaTechBonus));
                if($isBonus)
                {
                    $newArtifact->bonus_coef = $additionalSpace;
                    $this->space_max += $additionalSpace;
                }
                else
                {
                    $newArtifact->bonus_coef = 0-$additionalSpace;
                    $this->space_max -= $additionalSpace;
                }

                if(isset($options['maxEnding']))
                    unset($options['maxEnding']);

                $this->save();
            }
            elseif(in_array($newArtifact->bonus_category,['ColonyMax']))
            {
                $alreadyOwned = Artifact::whereIn('colony_id',$this->player->colonies->pluck('id')->toArray())->where('bonus_category','ColonyMax')->count();
                if($alreadyOwned > 0)
                    return $this->generateArtifact($options);
                $newArtifact->bonus_coef = 1;
                if(isset($options['maxEnding']))
                    unset($options['maxEnding']);
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }


}
