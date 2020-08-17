<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GateFight extends Model
{
    public function playerSource()
    {
        return $this->belongsTo('App\Player','id','player_id_source');
    }

    public function playerDest()
    {
        return $this->belongsTo('App\Player','id','player_id_dest');
    }

    public function playerWinner()
    {
        return $this->belongsTo('App\Player','id','player_id_winner');
    }

    public function colonySource()
    {
        return $this->belongsTo('App\Colony','id','colony_id_source');
    }

    public function colonyDest()
    {
        return $this->belongsTo('App\Colony','id','colony_id_dest');
    }
}
