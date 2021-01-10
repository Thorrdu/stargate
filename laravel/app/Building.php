<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony','building_colony','building_id','building_id')->withPivot('level');
    }

    public function requiredBuildings()
    {
        return $this->belongsToMany('App\Building','building_buildings','building_id','required_building_id')->withPivot('level');
    }

    public function requiredTechnologies()
    {
        return $this->belongsToMany('App\Technology','building_technologies','building_id','required_technology_id')->withPivot('level');
    }

    public function getPrice(int $level, $coef = 1)
    {
        //if($level == 1)
            $level--;
        $buildingPrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            if($this->$resource > 0)
                $buildingPrice[$resource] = $this->$resource * pow($this->upgrade_coefficient, $level) * $coef;
        }
        return $buildingPrice;
    }

    public function getEnergy(int $level)
    {
        //$level--; //Du au coeficient
        //return floor($this->energy_base * pow($this->energy_coefficient, $level));
        return floor($this->coefCalc($this->energy_base,$this->energy_coefficient,$level));
    }

    public function getTime(int $level)
    {
        //if($level == 1)
            $level--; //Du au coeficient
        return $this->time_base * pow($this->time_coefficient, $level);

        return $this->coefCalc($this->time_base,$this->time_coefficient,$level);
    }

    public function getProductionEnergy(int $level)
    {
        return $this->coefCalc($this->production_base,$this->production_coefficient,$level);
    }

    public function getProductionE2PZ(int $level)
    {
        return $this->coefCalc($this->production_base,$this->production_coefficient,$level);
    }

    public function getProduction(int $level)
    {
        if($level == 1)
            $level--; //Du au coeficient
        return floor($this->production_base * pow($this->production_coefficient, $level));
    }

    public function getConsumption(int $level)
    {
        if($level == 1)
            $level--; //Du au coeficient
        return floor($this->energy_base * pow($this->energy_coefficient, $level) );
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
