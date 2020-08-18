<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SpyLog extends Model
{
    public function playerSource()
    {
        return $this->belongsTo('App\Player','id','source_player_id');
    }

    public function playerDest()
    {
        return $this->belongsTo('App\Player','id','dest_player_id');
    }

    public function colonySource()
    {
        return $this->belongsTo('App\Colony','id','colony_source_id');
    }

    public function colonyDest()
    {
        return $this->belongsTo('App\Colony','id','colony_destination_id');
    }
}
