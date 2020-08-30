<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllianceRole extends Model
{
    public function alliance()
    {
        return $this->belongsTo('App\Alliance');
    }
    public function players()
    {
        return $this->hasMany('App\Player');
    }
}
