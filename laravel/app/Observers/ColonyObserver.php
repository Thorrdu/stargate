<?php

namespace App\Observers;

use App\Colony;
use App\Building;
use App\Technology;
use Illuminate\Support\Facades\DB;
use App\Reminder;
use Carbon\Carbon;

class ColonyObserver
{
    /**
     * Handle the colony "created" event.
     *
     * @param  \App\Colony  $colony
     * @return void
     */
    public function created(Colony $colony)
    {
        //
    }

    /**
     * Handle the colony "updated" event.
     *
     * @param  \App\Colony  $colony 
     * @return void
     */
    public function updating(Colony $colony)
    {
        echo PHP_EOL.'COLONY OBERSER EVENT UPDATED';

        try{
            if(is_null($colony->active_building_id) && $colony->isDirty('active_building_id'))
            {
                echo PHP_EOL.'player OBSRVER check requirements';
                //$colony->cast / $colony->original
                $endedBuilding = Building::find($colony->getOriginal('active_building_id'));
                $buildingsIds = [];
                $buildingEndedLvl = $colony->hasBuilding($endedBuilding);
                $buildingsIdsRaw = DB::table('building_buildings')->select('building_id')->where([['required_building_id',$endedBuilding->id],['level',$buildingEndedLvl]])->get()->toArray();
                foreach($buildingsIdsRaw as $raw)
                    $buildingsIds[] = $raw->building_id;
                $buildings = Building::whereIn('id',$buildingsIds)->get();
            
                foreach($buildings as $building)
                {               
                    $hasRequirements = true;
                    foreach($building->requiredTechnologies as $requiredTechnology)
                    {
                        $currentLvlOwned = $colony->player->hasTechnology($requiredTechnology);
                        if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                            $hasRequirements = false;
                    }
                    foreach($building->requiredBuildings as $requiredBuilding)
                    {
                        $currentLvlOwned = $colony->hasBuilding($requiredBuilding);
                        if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                            $hasRequirements = false;
                    }
                    if($hasRequirements)
                    {
                        $reminder = new Reminder;
                        $reminder->reminder_date = Carbon::now();
                        $reminder->reminder = trans('generic.buildingUnlocked', ['name' => trans('building.'.$building->slug.'.name', [], $colony->player->lang), 'planet' => $colony->name, 'coordinate' => $colony->coordinates->humanCoordinates()], $colony->player->lang);
                        $reminder->player_id = $colony->player->id;
                        $reminder->save();
                    }
                }
                
                $techIds = [];
                $techIdsRaw = DB::table('technology_buildings')->select('technology_id')->where([['required_building_id',$endedBuilding->id],['level',$buildingEndedLvl]])->get()->toArray();
                foreach($techIdsRaw as $raw)
                    $techIds[] = $raw->technology_id;
            
                $technologies = Technology::whereIn('id',$techIds)->get();
                foreach($technologies as $technology)
                {
                    $hasRequirements = true;
                    foreach($technology->requiredTechnologies as $requiredTechnology)
                    {
                        $currentLvlOwned = $colony->player->hasTechnology($requiredTechnology);
                        if(!($currentLvlOwned && $currentLvlOwned >= $requiredTechnology->pivot->level))
                            $hasRequirements = false;
                    }
                    foreach($technology->requiredBuildings as $requiredBuilding)
                    {
                        $currentLvlOwned = $colony->hasBuilding($requiredBuilding);
                        if(!($currentLvlOwned && $currentLvlOwned >= $requiredBuilding->pivot->level))
                            $hasRequirements = false;
                    }
                    if($hasRequirements)
                    {
                        $reminder = new Reminder;
                        $reminder->reminder_date = Carbon::now();
                        $reminder->reminder = trans('generic.researchUnlocked', ['name' => trans('research.'.$technology->slug.'.name', [], $colony->player->lang), 'planet' => $colony->name, 'coordinate' => $colony->coordinates->humanCoordinates()], $colony->player->lang);
                        $reminder->player_id = $colony->player->id;
                        $reminder->save();
                    }
                }
                //$colony->unsetEventDispatcher();
                //$colony->calcProd();
                echo PHP_EOL.'player OBSRVER TOP UPDATED';
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    }

    public function retrieved(Colony $colony)
    {
        //echo PHP_EOL.' Retrieved OBSERVER';

    }

    /**
     * Handle the colony "updated" event.
     *
     * @param  \App\Colony  $colony
     * @return void
     */
    public function saved(Colony $colony)
    {
        //echo PHP_EOL.'COLONY OBSERVER EVENT UPDATED 22222';
    }

    /**
     * Handle the colony "deleted" event.
     *
     * @param  \App\Colony  $colony
     * @return void
     */
    public function deleted(Colony $colony)
    {
        //
    }

    /**
     * Handle the colony "restored" event.
     *
     * @param  \App\Colony  $colony
     * @return void
     */
    public function restored(Colony $colony)
    {
        //
    }

    /**
     * Handle the colony "force deleted" event.
     *
     * @param  \App\Colony  $colony
     * @return void
     */
    public function forceDeleted(Colony $colony)
    {
        //
    }
}
