<?php

namespace App\Utility;

use App\Player;

class TopUpdater
{
    public static function update($player){
        if(!is_null($player))
        {
            $buildingPoints = 0;
            foreach($player->colonies as $colony)
            {
                foreach($colony->buildings as $building)
                {
                    $buildingPoints += TopUpdater::priceMerging($building->getPrice($building->pivot->level));
                }
            }
            $player->points_building = round($buildingPoints/1000);
            $player->points_total += $player->points_building;

            $researchPoints = 0;
            foreach($player->technologies as $technology)
            {
                $researchPoints += TopUpdater::priceMerging($technology->getPrice($technology->pivot->level));

            }
            $player->points_research = round($researchPoints/1000);
            $player->points_total += $player->points_research;


            $player->points_total += $player->points_military;
            $player->last_top_update = date("Y-m-d H:i:s");

            $player->save();
        }
    }

    public static function priceMerging($prices){
        $merging = 0;
        foreach ($prices as $resource => $price)
            $merging += $price;
        return $merging;
    }
}