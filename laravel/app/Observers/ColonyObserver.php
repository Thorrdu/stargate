<?php

namespace App\Observers;

use App\Colony;

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
    public function updated(Colony $colony)
    {
        echo PHP_EOL.'COLONY OBERSER EVENT UPDATED';
        if(is_null($colony->active_building_id) && $colony->isDirty('active_building_id'))
        {
            echo PHP_EOL.'OBSRVER FORCE RECALC';
            //$colony->unsetEventDispatcher();
            $colony->calcProd();
        }
    }

    public function retrieved(Colony $colony)
    {
        echo PHP_EOL.' Retrieved OBSERVER';
        $colony->checkProd();
        $colony->player->checkTechnology();
        $colony->checkBuilding();
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
