<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    public function player()
    {
        return $this->belongsTo('App\Player');
    }
}
