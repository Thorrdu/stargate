<?php

namespace App\Observers;

use App\Player;
use App\Building;
use App\Technology;
use Illuminate\Support\Facades\DB;
use App\Reminder;
use Carbon\Carbon;

class PlayerObserver
{
    /**
     * Handle the Player "created" event.
     *
     * @param  \App\Player  $player
     * @return void
     */
    public function created(Player $player)
    {
        //
    }

    /**
     * Handle the Player "updated" event.
     *
     * @param  \App\Player  $player 
     * @return void
     */
    public function updating(Player $player)
    {
        echo PHP_EOL.'Player OBERSER EVENT UPDATED';
        if(is_null($player->active_technology_id) && $player->isDirty('active_technology_id'))
        {
            echo PHP_EOL.'player OBSRVER check requirements';
            //$player->unsetEventDispatcher();
            //$player->calcProd();

            try{
                if(is_null($player->active_technology_id) && $player->isDirty('active_technology_id'))
                {
                    echo PHP_EOL.'OBSRVER top recalc';
                    //$colony->cast / $colony->original
                    $endedTech = Technology::find($player->getOriginal('active_technology_id'));
                    $buildingsIds = [];
                    $techEndedLvl = $player->hasTechnology($endedTech);
                    $buildingsIdsRaw = DB::table('building_technologies')->select('building_id')->where([['required_technology_id',$endedTech->id],['level',$techEndedLvl]])->get()->toArray();
                    foreach($buildingsIdsRaw as $raw)
                        $buildingsIds[] = $raw->building_id;
                    $buildings = Building::whereIn('id',$buildingsIds)->get();
                
                    foreach($buildings as $building)
                    {               
                        $hasRequirements = true;
                        foreach($building->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvlOwned = $player->hasTechnology($requiredTechnology);
                            if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;
                        }
                        foreach($building->requiredBuildings as $requiredBuilding)
                        {
                            $currentLvlOwned = $player->activeColony->hasBuilding($requiredBuilding);
                            if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                $hasRequirements = false;
                        }
                        if($hasRequirements)
                        {
                            $reminder = new Reminder;
                            $reminder->reminder_date = Carbon::now();
                            $reminder->reminder = trans('generic.buildingUnlocked', ['name' => trans('building.'.$building->slug.'.name', [], $player->lang)], $player->lang);
                            $reminder->player_id = $player->id;
                            $reminder->save();
                        }
                    }
                    
                    $techIds = [];
                    $techIdsRaw = DB::table('technology_technologies')->select('technology_id')->where([['required_technology_id',$endedTech->id],['level',$techEndedLvl]])->get()->toArray();
                    foreach($techIdsRaw as $raw)
                        $techIds[] = $raw->technology_id;
                
                    $technologies = Technology::whereIn('id',$techIds)->get();
                    foreach($technologies as $technology)
                    {
                        $hasRequirements = true;
                        foreach($technology->requiredTechnologies as $requiredTechnology)
                        {
                            $currentLvlOwned = $player->hasTechnology($requiredTechnology);
                            if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                                $hasRequirements = false;
                        }
                        foreach($technology->requiredBuildings as $requiredBuilding)
                        {
                            $currentLvlOwned = $player->activeColony->hasBuilding($requiredBuilding);
                            if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                                $hasRequirements = false;
                        }
                        if($hasRequirements)
                        {
                            $reminder = new Reminder;
                            $reminder->reminder_date = Carbon::now();
                            $reminder->reminder = trans('generic.researchUnlocked', ['name' => trans('research.'.$technology->slug.'.name', [], $player->lang)], $player->lang);
                            $reminder->player_id = $player->id;
                            $reminder->save();
                        }
                    }
                    //$colony->unsetEventDispatcher();
                    //$colony->calcProd();
                }
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }

    public function retrieved(Player $player)
    {
        //
    }

    /**
     * Handle the Player "updated" event.
     *
     * @param  \App\Player  $player
     * @return void
     */
    public function saved(Player $player)
    {
        //echo PHP_EOL.'Player OBSERVER EVENT UPDATED 22222';
    }

    /**
     * Handle the Player "deleted" event.
     *
     * @param  \App\Player  $player
     * @return void
     */
    public function deleted(Player $player)
    {
        //
    }

    /**
     * Handle the Player "restored" event.
     *
     * @param  \App\Player  $player
     * @return void
     */
    public function restored(Player $player)
    {
        //
    }

    /**
     * Handle the Player "force deleted" event.
     *
     * @param  \App\Player  $player
     * @return void
     */
    public function forceDeleted(Player $player)
    {
        //
    }
}
