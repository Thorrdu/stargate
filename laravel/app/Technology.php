<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    public function players()
    {
        return $this->belongsToMany('App\Player')->withPivot('level');
    }

    public function requiredBuildings()
    {
        return $this->belongsToMany('App\Building','technology_buildings','technology_id','required_building_id')->withPivot('level');
    }

    public function requiredTechnologies()
    {
        return $this->belongsToMany('App\Technology','technology_technologies','technology_id','required_technology_id')->withPivot('level');
    }

    public function getPrice(int $level, $coef = 1)
    {
        $level--; //Du au coeficient
        $buildingPrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            if($this->$resource > 0)
                $buildingPrice[$resource] = $this->$resource * pow($this->upgrade_coefficient, $level) * $coef;
                //$buildingPrice[$resource] = $this->coefCalc($this->$resource,$this->upgrade_coefficient,$level);
        }
        return $buildingPrice;
    }

    public function getTime(int $level)
    {
        $level--; //Du au coeficient
        return $this->time_base * pow($this->time_coefficient, $level);
        //return $this->coefCalc($this->time_base,$this->time_coefficient,$level);

    }

    
    public function coefCalc($base,$coef,$level)
    {
        $returnValue = $base;
        for($cpt = 1; $cpt <= $level; $cpt++)
        {
            if($cpt > 1)
            {
                $returnValue += ($base*pow($coef,$cpt));
            }
        }
        return $returnValue;
    }
}
