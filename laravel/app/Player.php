<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Utility\TopUpdater;
use App\Coordinate;
use App\Trades;
use App\GateFight;

class Player extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::updating(function($player) {
            //dd($player);
        });
    }

    public function activeColony()
    {
        return $this->hasOne('App\Colony','id','active_colony_id');
    }

    public function colonies()
    {
        return $this->hasMany('App\Colony');
    }

    public function explorations()
    {
        return $this->hasMany('App\Exploration');
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

    public function reminders()
    {
        return $this->hasMany('App\Reminder');
    }

    public function addColony(Coordinate $choosedCoordinate = null)
    {
        try{
            $newColony = new Colony;
            $newColony->colony_type = 1;
            $newColony->player_id = $this->id;
            $newColony->name = 'P'.rand(1, 9).Str::upper(Str::random(1)).'-'.rand(1, 9).rand(1, 9).rand(1, 9);
            $newColony->last_claim = date("Y-m-d H:i:s");  

            if($choosedCoordinate == null && $this->user_id == 125641223544373248)
            {
                $coordinate = Coordinate::where([['galaxy', 1],['system', 1],['planet', 1],['colony_id', null]])->first();
                $newColony->coordinate_id = $coordinate->id;
                $newColony->space_max = 200;
                $newColony->name = 'Asgard';
            }
            elseif($choosedCoordinate == null)
            {
                $coordinate = Coordinate::where('colony_id', null)->inRandomOrder()->limit(1)->first();
                $newColony->coordinate_id = $coordinate->id;
                $newColony->space_max = 180;
            }
            else
            {
                $minSpace = $maxSpace = 0;
                if($choosedCoordinate->planet < 3)
                {
                    $minSpace = 10;
                    $maxSpace = 100;
                }
                elseif($choosedCoordinate->planet < 7)
                {
                    $minSpace = 150;
                    $maxSpace = 200;
                }
                elseif($choosedCoordinate->planet < 9)
                {
                    $minSpace = 100;
                    $maxSpace = 150;
                }
                else
                {
                    $minSpace = 50;
                    $maxSpace = 100;
                }
                $newColony->space_max = rand($minSpace,$maxSpace);
                $newColony->coordinate_id = $choosedCoordinate->id;
                $coordinate = $choosedCoordinate;
            }
            $newColony->save();

            $coordinate->colony_id = $newColony->id;
            $coordinate->save();

            $this->colonies->push($newColony);
            $this->active_colony_id = $newColony->id;
            $this->save();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    public function removeColony(Colony $colony)
    {
        $colony->buildings()->detach();
        $colony->units()->detach();

        $trades = Trade::where('coordinate_source_id', $colony->coordinates->id)->orWhere('coordinate_destination_id', $colony->coordinates->id)->get();
        foreach($trades as $trade)
        {
            foreach($trades->tradeResources as $tradeResource)
                $tradeResource->delete();
            $trade->delete();
        }
        $gateFigthts = GateFight::where('colony_id_source', $colony->id)->orWhere('colony_id_dest', $colony->id)->get();
        foreach($gateFigthts as $gateFight)
            $gateFight->delete();
        
        if($this->active_colony_id == $colony->id)
        {
            $this->active_colony_id = $this->colonies[0]->id;
            $this->save();
        }
        $colony->coordinate->colony_id = null;
        $colony->coordinate->save();
        $colony->delete();
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
        $buildingTime *= $this->activeColony->getResearchBonus();

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_technology_id = $technology->id;
        $this->active_technology_end = $buildingEnd;
        $this->save();

        $buildingPrices = $technology->getPrice($wantedLvl);
        foreach (config('stargate.resources') as $resource)
        {
            if($technology->$resource > 0)
                $this->activeColony->$resource -= round($buildingPrices[$resource]);
        }

        if($this->notification)
        {
            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->addSecond($buildingTime);
            $reminder->reminder = "**Lvl ".$wantedLvl." - ".trans('research.'.$technology->slug.'.name', [], $this->player->lang)."** ".trans("reminder.isDone", [], $this->lang);
            $reminder->player_id = $this->id;
            $reminder->save();
            //$this->player->reminders()->attach($reminder->id);
        }

        $this->activeColony->save();
        //$this->save();
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
                $this->load('technologies'); // solution avec query
            }

            $this->active_technology_id = null;
            $this->active_technology_end = null;
            foreach($this->colonies as $colony)
            {
                $colony->calcProd();
                $colony->save();
            }
            //$this->activeColony->saveWithoutEvents();
            $this->save();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }
    
    public function isWeakOrStrong(Player $player2)
    {
        config('stargate.gateFight.StrongWeak');
        
        if($this->points_total > config('stargate.gateFight.StrongWeak') && $player2->points_total > config('stargate.gateFight.StrongWeak'))
            return '';
        elseif($player2->points_total > ($this->points_total*2))
            return '[S] ';
        elseif($player2->points_total < ($this->points_total/2))
            return '[W] ';
        else
            return '';
    }
    public function isRaidable(Player $player2)
    {  
        if($this->points_total > config('stargate.gateFight.StrongWeak') && $player2->points_total > config('stargate.gateFight.StrongWeak'))
            return true;
        elseif($player2->points_total > ($this->points_total*2) || $player2->points_total < ($this->points_total/2))
            return false;
        else
            return true;
    }
}
