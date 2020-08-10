<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TradeResource extends Model
{
    public function trade()
    {
        return $this->belongsTo('App\Trade');
    }
}
