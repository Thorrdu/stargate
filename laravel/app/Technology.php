<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    public function players()
    {
        return $this->belongsToMany('App\Player')->withPivot('level');
    }
}
