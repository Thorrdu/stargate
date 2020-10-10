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
        //echo PHP_EOL.'COLONY OBERSER EVENT UPDATED';
        /*
        try{
            if(is_null($colony->active_building_id) && $colony->isDirty('active_building_id'))
            {
                //echo PHP_EOL.'player OBSRVER check requirements';
                //$colony->cast / $colony->original
                //Notifications supprimées en 0.7.1
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }*/
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
