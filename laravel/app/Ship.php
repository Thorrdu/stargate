<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ship extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony')->withPivot('number');
    }

    public function fleets()
    {
        return $this->belongsToMany('App\Fleet')->withPivot('number');
    }

    public function player()
    {
        return $this->belongsTo('App\Player');
    }

    public function shipQueues()
    {
        return $this->belongsToMany('App\Colony','ship_queues','ship_id','colony_id')->withPivot('ship_end');
    }

    public function getPrice(int $qty, $coef = 1)
    {
        $buildingPrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            if($this->$resource > 0)
                $buildingPrice[$resource] = $this->$resource * $qty * $coef;
        }
        return $buildingPrice;
    }

    public function toStrting($lang='fr')
    {
        return $this->name.' ('.config('stargate.emotes.armament').' '.number_format($this->fire_power).', '.config('stargate.emotes.shield').' '.number_format($this->shield).', '.config('stargate.emotes.hull').' '.number_format($this->hull).')';
    }
}
