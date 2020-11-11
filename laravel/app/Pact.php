<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pact extends Model
{
    public function player1()
    {
        return $this->belongsTo('App\Player','player_1_id','id');
    }

    public function player2()
    {
        return $this->belongsTo('App\Player','player_2_id','id');
    }
}
