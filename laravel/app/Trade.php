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
        if($this->trade_value_player2 > 0)
            $diff2 = $this->trade_value_player2;

        $p1HasGivenPremium = $p2HasGivenPremium = false;
        foreach($this->tradeResources as $tradeResource)
        {
            if($tradeResource->resource == 'premium')
                ${'p'.$tradeResource->player.'HasPremium'} = true;
        }

        if($this->playerSource->points_total > $this->playerDest->points_total && ($this->trade_value_player2/$diff1) > 2)
        {
            //Si player 1 est plus fort et à reçu plus de 15% en plus que player 2
            if($p1HasGivenPremium && !$p2HasGivenPremium)
                return true;
            else
                return false;
        }
        elseif($this->playerSource->points_total < $this->playerDest->points_total && ($this->trade_value_player1/$diff2) > 2)
        {
            //Si player 2 est plus fort et à reçu plus de 15% en plus que player 1
            if($p2HasGivenPremium && !$p1HasGivenPremium)
                return true;
            else
                return false;
        }
        return true;
    }
}
