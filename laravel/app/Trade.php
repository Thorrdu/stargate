<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    public function playerSource()
    {
        return $this->belongsTo('App\Player','player_id_source','id');
    }

    public function playerDest()
    {
        return $this->belongsTo('App\Player','player_id_dest','id');
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

    public function getFairness()
    {
        $diff1 = 1;
        if($this->trade_value_player1 > 0)
            $diff1 = $this->trade_value_player1;
        $diff2 = 1;
        if($this->trade_value_player1 > 0)
            $diff2 = $this->trade_value_player2;
        if($this->playerSource->points_total > $this->playerDest->points_total && ($this->trade_value_player2/$diff1) > 1.25)
        {
            //Si player 1 est plus fort et à donné plus de 15% en plus que player 2
            return false;
        }
        elseif($this->playerSource->points_total < $this->playerDest->points_total && ($this->trade_value_player1/$diff2) > 1.25)
        {
            //Si player 2 est plus fort et à donné plus de 15% en plus que player 1
            return false;
        }
        return true;

    }
}
