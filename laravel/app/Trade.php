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

    public function coordinateSource()
    {
        return $this->belongsTo('App\Coordinate','id','coordinate_source_id');
    }

    public function coordinateDest()
    {
        return $this->belongsTo('App\Coordinate','id','coordinate_destination_id');
    }

    public function tradeResources()
    {
        return $this->hasMany('App\TradeResource');
    }

    public function setTradeValue()
    {
        $this->trade_value = 0;
        foreach($this->tradeResources as $tradeResource)
        {
            $this->trade_value += $tradeResource->trade_value;
        }
    }
}
