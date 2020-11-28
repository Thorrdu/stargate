<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Coordinate;
use App\Trades;
use App\Artifact;
use App\Exploration;
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

    public function activeTechnologyColony()
    {
        return $this->hasOne('App\Colony','id','active_technology_colony_id');
    }

    public function colonies()
    {
        return $this->hasMany('App\Colony')->orderBy('colonies.id','ASC');
    }

    public function artifacts()
    {
        return $this->hasManyThrough('App\Artifact', 'App\Colony');
    }

    public function ships()
    {
        return $this->hasMany('App\Ship');
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

    public function alliance()
    {
        return $this->belongsTo('App\Alliance');
    }

    public function allianceRole()
    {
        return $this->belongsTo('App\AllianceRole','role_id','id');
    }

    public function incomingFleets()
    {
        return $this->hasMany('App\Fleet','player_destination_id','id')->where([['returning', false],['fleets.ended', false],['fleets.mission', '!=' , 'scavenge']]);
    }

    public function activeFleets()
    {
        return $this->hasMany('App\Fleet','player_source_id','id')->where('fleets.ended','false');
    }

    public function addColony(Coordinate $choosedCoordinate = null)
    {
        try{
            $newColony = new Colony;
            if($this->colonies->count() > 0)
                $newColony->military = 1000;
            else
                $newColony->military = 100;
            $newColony->colony_type = 1;
            $newColony->player_id = $this->id;
            if($this->colonies->count() == 0)
                $newColony->prime_colony = true;
            $newColony->name = 'P'.rand(1, 9).Str::upper(Str::random(1)).'-'.rand(1, 9).rand(1, 9).rand(1, 9);
            $newColony->last_claim = date("Y-m-d H:i:s");
            $newColony->artifact_check = Carbon::now()->add(rand(1,72).'h');
            $newColony->image = rand(1,34).'.png';

            if($choosedCoordinate == null)
            {
                $coordinate = Coordinate::where('colony_id', null)->inRandomOrder()->limit(1)->first();
                $newColony->coordinate_id = $coordinate->id;
                $newColony->space_max = 180;
            }
            else
            {
                $minSpace = $maxSpace = 0;
                if($this->user_id == 125641223544373248)
                {
                    $minSpace = 200;
                    $maxSpace = 200;
                }
                elseif($choosedCoordinate->planet < 3)
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function removeColony(Colony $colony)
    {
        try{
            $colony->buildings()->detach();
            $colony->units()->detach();
            $colony->defences()->detach();
            $colony->ships()->detach();
            $colony->craftQueues()->detach();
            $colony->shipQueues()->detach();
            $colony->defenceQueues()->detach();

            $gateFigthts = GateFight::where('colony_id_source', $colony->id)->orWhere('colony_id_dest', $colony->id)->get();
            foreach($gateFigthts as $gateFight)
                $gateFight->delete();

            $spyLogs = SpyLog::where('colony_source_id', $colony->id)->orWhere('colony_destination_id', $colony->id)->get();
            foreach($spyLogs as $spyLog)
                $spyLog->delete();

            $fleets = Fleet::where('colony_source_id', $colony->id)->orWhere('colony_destination_id', $colony->id)->get();
            foreach($fleets as $fleet)
            {
                $fleet->ships()->detach();
                $fleet->delete();
            }

            $explorationLogs = Exploration::where('colony_source_id', $colony->id)->get();
            foreach($explorationLogs as $explorationLog)
                $explorationLog->delete();

            $artifacts = Artifact::where('colony_id', $colony->id)->get();
            foreach($artifacts as $artifact)
                $artifact->delete();

            if($this->activeColony->id == $colony->id)
            {
                $this->active_colony_id = $this->colonies[0]->id;
                $this->save();
            }
            $coordinates = $colony->coordinates;
            $coordinates->colony_id = null;
            $coordinates->save();
            $colony->coordinates = null;
            $colony->delete();
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
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
        $buildingTime *= $this->activeColony->getResearchBonus($technology->id);

        $buildingEnd = $current->addSeconds($buildingTime);

        $this->active_technology_id = $technology->id;
        $this->active_technology_colony_id = $this->activeColony->id;
        $this->active_technology_end = $buildingEnd;
        $this->save();

        $coef = $this->activeColony->getArtifactBonus(['bonus_category' => 'Price', 'bonus_type' => 'Research']);

        $buildingPrices = $technology->getPrice($wantedLvl, $coef);
        foreach (config('stargate.resources') as $resource)
        {
            if($technology->$resource > 0)
                $this->activeColony->$resource -= $buildingPrices[$resource];
            if($this->activeColony->$resource < 0)
                $this->activeColony->$resource = 0;
        }

        $this->activeColony->save();
        //$this->save();
        return $this->active_technology_end;
    }

    public function checkTechnology()
    {
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
            $newLvl = 1;
            $technologyExists = $this->technologies->filter(function ($value) use($technology){
                return $value->id == $technology->id;
            });
            if($technologyExists->count() > 0)
            {
                $technologyToUpgrade = $technologyExists->first();
                $technologyToUpgrade->pivot->level++;
                $technologyToUpgrade->pivot->save();
                $newLvl = $technologyToUpgrade->pivot->level;
            }
            else
            {
                $this->technologies()->attach([$technology->id => ['level' => 1]]);
                $this->load('technologies'); // solution avec query
            }

            $this->active_technology_id = null;
            $this->active_technology_colony_id = null;
            $this->active_technology_end = null;
            foreach($this->colonies as $colony)
            {
                $colony->calcProd();
                $colony->save();
            }
            //$this->activeColony->saveWithoutEvents();
            $this->save();

            if($this->notification)
            {
                $reminder = new Reminder;
                $reminder->reminder_date = Carbon::now()->addSecond(1);
                $reminder->reminder = $this->activeColony->name." [".$this->activeColony->coordinates->humanCoordinates()."] **Lvl ".$newLvl." - ".trans('research.'.$technology->slug.'.name', [], $this->lang)."** ".trans("reminder.isDone", [], $this->lang);
                $reminder->player_id = $this->id;
                $reminder->save();
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }

    public function isWeakOrStrong(Player $player2)
    {
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

    public function getShipSpeedBonus()
    {
        $bonus = 1;

        $technologies = $this->technologies->filter(function ($value){
            return !is_null($value->ship_speed_bonus) && $value->ship_speed_bonus > 0;
        });
        foreach($technologies as $technology)
        {
            $bonus *= pow($technology->ship_speed_bonus, $technology->pivot->level);
        }

        return $bonus;
    }

    public function getShipConsumptionBonus()
    {
        $bonus = 1;

        $technologies = $this->technologies->filter(function ($value){
            return !is_null($value->ship_consumption_bonus) && $value->ship_consumption_bonus > 0;
        });
        foreach($technologies as $technology)
        {
            $bonus *= pow($technology->ship_consumption_bonus, $technology->pivot->level);
        }

        return $bonus;
    }

    public function checkFleets()
    {
        $checkedFleet = 0;
        foreach($this->activeFleets as $activeFleet)
        {
            $arrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$activeFleet->arrival_date);
            if($arrivalDate->isPast()){
                $activeFleet->outcome();
                $checkedFleet++;
            }
        }
        foreach($this->incomingFleets as $incomingFleet)
        {
            $arrivalDate = Carbon::createFromFormat("Y-m-d H:i:s",$incomingFleet->arrival_date);
            if($arrivalDate->isPast()){
                $incomingFleet->outcome();
                $checkedFleet++;
            }
        }
        if($checkedFleet > 0){
            $this->load('activeFleets');
            $this->load('incomingFleets');
        }
    }
}
