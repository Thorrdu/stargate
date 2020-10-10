<?php

namespace App\Utility;

use App\Player;

class TopUpdater
{
    public static function update($player){
        if(!is_null($player) && !$player->npc)
        {
            try{
                echo PHP_EOL.'Top Recalc: '.$player->user_name;

                $player->old_points_craft = $player->points_craft;
                $player->old_points_military = $player->points_military;
                $player->old_points_defence = $player->points_defence;
                $player->old_points_building = $player->points_building;
                $player->old_points_research = $player->points_research;
                $player->old_points_total = $player->points_total;

                if($player->vacation)
                {
                    $player->points_craft = 0;
                    $player->points_military = 0;
                    $player->points_defence = 0;
                    $player->points_building = 0;
                    $player->points_research = 0;

                    $player->points_total = 0;
                    $player->save();
                    return;
                }

                $buildingPoints = 0;
                $player->points_total = 0;
                $militaryPoint = 0;
                $defencePoints = 0;
                $craftPoint = 0;
                foreach($player->colonies as $colony)
                {
                    foreach($colony->buildings as $building)
                    {
                        for($cptPoint = 1;$cptPoint <= $building->pivot->level; $cptPoint++)
                            $buildingPoints += TopUpdater::priceMerging($building->getPrice($cptPoint));
                    }
                    $militaryPoint += $colony->military * 0.2;

                    foreach($colony->units as $unit)
                        $craftPoint += TopUpdater::priceMerging($unit->getPrice($unit->pivot->number));
                    foreach($colony->defences as $defence)
                        $defencePoints += TopUpdater::priceMerging($defence->getPrice($defence->pivot->number));
                    foreach($colony->ships as $ship)
                        $militaryPoint += TopUpdater::priceMerging($ship->getPrice($ship->pivot->number));
                }

                $player->points_craft = round($craftPoint/1000);
                $player->points_military = round($militaryPoint/1000);
                $player->points_defence = round($defencePoints/1000);
                $player->points_building = round($buildingPoints/1000);
                $player->points_total += $player->points_building + $player->points_defence + $player->points_military;

                $researchPoints = 0;
                foreach($player->technologies as $technology)
                {
                    for($cptPoint = 1;$cptPoint <= $technology->pivot->level; $cptPoint++)
                        $researchPoints += TopUpdater::priceMerging($technology->getPrice($cptPoint));
                }
                $player->points_research = round($researchPoints/1000);
                $player->points_total += $player->points_research;
                $player->last_top_update = date("Y-m-d H:i:s");
                $player->save();
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }


    public static function updateAlliance($alliance){
        try{
            echo PHP_EOL.'Top Alliance Recalc: '.$alliance->name;

            $alliance->old_points_craft = $alliance->points_craft;
            $alliance->old_points_military = $alliance->points_military;
            $alliance->old_points_defence = $alliance->points_defence;
            $alliance->old_points_building = $alliance->points_building;
            $alliance->old_points_research = $alliance->points_research;
            $alliance->old_points_total = $alliance->points_total;

            $alliance->points_craft = $alliance->members->avg('points_craft');
            $alliance->points_military = $alliance->members->avg('points_military');
            $alliance->points_defence = $alliance->members->avg('points_defence');
            $alliance->points_building = $alliance->members->avg('points_building');
            $alliance->points_research = $alliance->members->avg('points_research');
            $alliance->points_total = $alliance->members->avg('points_total');

            $alliance->last_top_update = date("Y-m-d H:i:s");
            $alliance->save();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }

    }

    public static function priceMerging($prices){
        $merging = 0;
        foreach ($prices as $resource => $price)
            $merging += $price;
        return $merging;
    }
}
