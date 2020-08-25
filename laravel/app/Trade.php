<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
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
        return $this->belongsTo('App\Colony','id','colony__source_id');
    }

    public function colonyDest()
    {
        return $this->belongsTo('App\Colony','id','colony_destination_id');
    }

    public function tradeResources()
    {
        return $this->hasMany('App\TradeResource');
    }

    public function setTradeValue()
    {
        $this->{'trade_value_player1'} = 0;
        $this->{'trade_value_player2'} = 0;
        foreach($this->tradeResources as $tradeResource)
        {
            $this->{'trade_value_player'.$tradeResource->player} += $tradeResource->trade_value;
        }
    }
}
