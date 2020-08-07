<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coordinate extends Model
{
    public function colony()
    {
        return $this->hasOne('App\Colony');
    }
}
