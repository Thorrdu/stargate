<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShipPart extends Model
{
    public function requiredBuildings()
    {
        return $this->belongsToMany('App\Building','ship_part_buildings','ship_part_id','required_building_id')->withPivot('level');
    }

    public function requiredTechnologies()
    {
        return $this->belongsToMany('App\Technology','ship_part_technologies','ship_part_id','required_technology_id')->withPivot('level');
    }

    public function getPrice()
    {
        $buildingPrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            if($this->$resource > 0)
                $buildingPrice[$resource] = $this->$resource;
        }
        return $buildingPrice;
    }
}
