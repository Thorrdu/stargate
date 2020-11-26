<?php

namespace App;

use App\Utility\FuncUtility;
use Illuminate\Database\Eloquent\Model;

class Exploration extends Model
{
    public function colonySource()
    {
        return $this->hasOne('App\Colony','id','colony_source_id');
    }

    public function coordinateDestination()
    {
        return $this->hasOne('App\Coordinate','id','coordinate_destination_id');
    }

    public function player(){
        return $this->belongsTo('App\Player');
    }

    public function outcome()
    {
        $randomEvent = rand(1,100);

        if($randomEvent <= 14)
        {
            $this->exploration_result = false;
            $this->save();

            $this->colonySource->military += 1000;
            $this->colonySource->save();
            $possibilities = ['exploreFailed', 'exploreFailed2'];
            return trans('stargate.'.$possibilities[rand(0,1)], ['coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        elseif($randomEvent <= 45 && $this->colonySource->artifacts->count() < 10)
        {
            $this->exploration_result = true;
            $this->exploration_outcome = 'Artifact';
            $this->colonySource->military += 1000;
            $this->colonySource->save();

            $this->save();
            $newArtifact = $this->colonySource->generateArtifact(array('maxEnding'=> 72))->toString($this->player->lang);

            return trans('stargate.exploreSucessArtifact', ['coordinates' => $this->coordinateDestination->humanCoordinates(), 'artifact' => $newArtifact], $this->player->lang);
        }
        elseif($randomEvent <= 65)
        {
            //Craft aléatoire
            $randomUnit = Unit::where('id','<', 6)->get()->random();
            $resValue = rand(1,4);
            $resourceString = ucfirst(trans('craft.'.$randomUnit->slug.'.name', [], $this->player->lang)).': '.number_format($resValue);

            $this->exploration_result = true;
            $this->exploration_outcome = 'Unit';
            $this->unit_id = $randomUnit->id;
            $this->outcome_quantity = $resValue;
            $this->save();

            $unitExists = $this->colonySource->units->filter(function ($value) use($randomUnit){
                return $value->id == $randomUnit->id;
            });
            if($unitExists->count() > 0)
            {
                $unitToUpdate = $unitExists->first();
                $unitToUpdate->pivot->number += $resValue;
                $unitToUpdate->pivot->save();
            }
            else
            {
                $this->colonySource->units()->attach([$randomUnit->id => ['number' => $resValue]]);
            }

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessResources', ['resources' => $resourceString, 'coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        elseif($randomEvent <= 95)
        {
            $resNumber = rand(1,4);
            //Ressource aléatoire
            $resources = array( 'iron' => 3,
                        'gold' => 2,
                        'quartz' => 1,
                        'naqahdah' => 1,
                        'E2PZ' => 1);
            $resNumberWeight = array(
                1 => 4,
                2 => 3,
                3 => 2,
                4 => 1,
            );
            $resNumber = FuncUtility::rand_with_weight($resNumberWeight);

            $refounds = [];
            $resourceString = '';
            foreach(range(1,$resNumber) as $n)
            {
                $resType = FuncUtility::rand_with_weight($resources);
                $varProd = 'production_'.$resType;

                if($resType == 'E2PZ')
                    $resValue = rand(2,6);
                else
                    $resValue = $this->colonySource->$varProd * rand(2,4);

                $this->colonySource->$resType += $resValue;

                if(isset($refounds[$resType]))
                    $refounds[$resType] += $resValue;
                else
                    $refounds[$resType] = $resValue;
            }
            foreach($refounds as $resFound => $resQty)
                $resourceString .= config('stargate.emotes.'.strtolower($resFound))." ".ucfirst($resFound).': '.number_format($resQty)."\n";

            $this->exploration_result = true;
            $this->exploration_outcome = 'Resource';
            $this->outcome_resource = $resType;
            $this->outcome_quantity = $resValue;
            $this->save();

            $this->colonySource->military += 1000;
            $this->colonySource->save();

            return trans('stargate.exploreSucessResources', ['resources' => $resourceString, 'coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
        else
        {
            $this->exploration_result = false;
            $this->save();
            return trans('stargate.exploreCriticalFailed', ['coordinates' => $this->coordinateDestination->humanCoordinates()], $this->player->lang);
        }
    }
}
