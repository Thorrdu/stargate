<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alliance extends Model
{
    public function members()
    {
        return $this->hasMany('App\Player','alliance_id',"id");
    }
    public function founder()
    {
        return $this->belongsTo('App\Player','founder_id','id');
    }
    public function leader()
    {
        return $this->belongsTo('App\Player','leader_id','id');
    }
    public function roles()
    {
        return $this->hasMany('App\AllianceRole');
    }
}
