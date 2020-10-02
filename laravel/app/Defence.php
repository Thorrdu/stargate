<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Defence extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony')->withPivot('number');
    }

    public function requiredBuildings()
    {
        return $this->belongsToMany('App\Building','defence_buildings','defence_id','required_building_id')->withPivot('level');
    }

    public function requiredTechnologies()
    {
        return $this->belongsToMany('App\Technology','defence_technologies','defence_id','required_technology_id')->withPivot('level');
    }

    public function defenceQueues()
    {
        return $this->belongsToMany('App\Colony','defence_queues','defence_id','colony_id')->withPivot('defence_end');
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
}
