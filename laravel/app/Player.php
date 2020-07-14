<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Player extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::updating(function($player) {
            //dd($player);
        });
    }

    public function colonies()
    {
        return $this->hasMany('App\Colony');
    }

    public function addColony()
    {
        $newColony = new Colony;
        $newColony->colony_type = 1;
        $newColony->player_id = $this->id;
        $newColony->name = 'P'.rand(1, 9).Str::random(1).'-'.rand(1, 9).rand(1, 9).rand(1, 9);
        $newColony->save();

        $this->colonies->push($newColony);
    }
}
