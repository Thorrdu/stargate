<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony')->withPivot('number');
    }
}
