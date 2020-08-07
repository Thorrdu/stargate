<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony')->withPivot('number');
    }

    public function requiredBuildings()
    {
        return $this->belongsToMany('App\Building','unit_buildings','unit_id','required_building_id')->withPivot('level');
    }

    public function requiredTechnologies()
    {
        return $this->belongsToMany('App\Technology','unit_technologies','unit_id','required_technology_id')->withPivot('level');
    }

    public function craftQueues()
    {
        return $this->belongsToMany('App\Colony','craft_queues','unit_id','colony_id')->withPivot('craft_end');
    }

    public function getPrice(int $qty)
    {
        $buildingPrice = [];
        foreach (config('stargate.resources') as $resource)
        {
            if($this->$resource > 0)
                $buildingPrice[$resource] = $this->$resource * $qty;
            //eventuel bonus de réduction
        }
        return $buildingPrice;
    }
}
