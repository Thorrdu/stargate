<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coordinate extends Model
{
    public function colonies()
    {
        return $this->belongsTo('App\Colony');
    }

    public function colony()
    {
        return $this->belongsTo('App\Colony','id','coordinate_id');
    }

    public function humanCoordinates()
    {
        return $this->galaxy.':'.$this->system.':'.$this->planet;
    }
}
