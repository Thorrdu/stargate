<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    public function players()
    {
        return $this->belongsToMany('App\Player')->withPivot('level');
    }

    public function getPrice(int $level)
    {
        $level--; //Du au coeficient
        $buildingPrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            if($this->$resource > 0)
                $buildingPrice[$resource] = $this->$resource * pow($this->upgrade_coefficient, $level);
        }
        return $buildingPrice;
    }

    public function getTime(int $level)
    {
        $level--; //Du au coeficient
        return $this->time_base * pow($this->time_coefficient, $level);
    }
}
