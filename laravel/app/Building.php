<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    public function colonies()
    {
        return $this->belongsToMany('App\Colony')->withPivot('level');
    }
}
