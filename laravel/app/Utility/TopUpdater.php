<?php

namespace App\Utility;

use App\Player;

class TopUpdater
{
    public static function update($player){
        if(!is_null($player))
        {
            try{
                echo PHP_EOL.'Top Recalc: '.$player->user_name;
                $buildingPoints = 0;
                $player->points_total = 0;
                foreach($player->colonies as $colony)
                {
                    foreach($colony->buildings as $building)
                    {
                        for($cptPoint = 1;$cptPoint <= $building->pivot->level; $cptPoint++)
                            $buildingPoints += TopUpdater::priceMerging($building->getPrice($cptPoint));
                    }
                }
                $player->points_building = round($buildingPoints/1000);
                $player->points_total += $player->points_building;

                $researchPoints = 0;
                foreach($player->technologies as $technology)
                {
                    for($cptPoint = 1;$cptPoint <= $technology->pivot->level; $cptPoint++)
                        $researchPoints += TopUpdater::priceMerging($technology->getPrice($cptPoint));
                }
                $player->points_research = round($researchPoints/1000);
                $player->points_total += $player->points_research;

                $militaryPoint = 0;
                foreach($player->colonies[0]->units as $unit)
                {

                    $militaryPoint += TopUpdater::priceMerging($unit->getPrice($unit->pivot->number));
                }
                echo PHP_EOL."FINMIL";

                $player->points_military = round($militaryPoint/1000);
                
                $player->points_total += $player->points_military;
                $player->last_top_update = date("Y-m-d H:i:s");

                $player->save();
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }

    public static function priceMerging($prices){
        $merging = 0;
        foreach ($prices as $resource => $price)
            $merging += $price;
        return $merging;
    }
}