<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony')->withPivot('level');
    }

    public function requiredBuildings()
    {
        return $this->belongsToMany('App\Building')->withPivot('level');
    }

    public function requiredTechnologies()
    {
        return $this->belongsToMany('App\Technology')->withPivot('level');
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

    public function getEnergy(int $level)
    {
        $level--; //Du au coeficient
        return $this->energy_base * pow($this->energy_coefficient, $level);
    }

    public function getTime(int $level)
    {
        $level--; //Du au coeficient
        return $this->time_base * pow($this->time_coefficient, $level);
    }

    public function getProduction(int $level)
    {
        $level--; //Du au coeficient
        return $this->production_base * pow($this->production_coefficient, $level);
    }
}
