<?php

namespace App\Observers;

use App\Player;
use App\Utility\TopUpdater;

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
            echo PHP_EOL.'player OBSRVER top recalc';
            //$player->unsetEventDispatcher();
            //$player->calcProd();
            TopUpdater::update($player); 
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
