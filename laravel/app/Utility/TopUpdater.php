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
                        $debugString = " ";
                        $debugPrice = $building->getPrice($building->pivot->level);
                        foreach ($debugPrice as $resource => $price)
                            $debugString .= $price.' '.$resource." ";
                        echo PHP_EOL.'Lvl '.$building->pivot->lvl.' '.$building->name.' - '.TopUpdater::priceMerging($building->getPrice($building->pivot->number)).'Points ( '.$debugString.' )'; 

                        $buildingPoints += TopUpdater::priceMerging($building->getPrice($building->pivot->level));
                    }
                }
                $player->points_building = round($buildingPoints/1000);
                $player->points_total += $player->points_building;

                $researchPoints = 0;
                foreach($player->technologies as $technology)
                {
                    $debugString = " ";
                    $debugPrice = $technology->getPrice($technology->pivot->level);
                    foreach ($debugPrice as $resource => $price)
                        $debugString .= $price.' '.$resource." ";
                    echo PHP_EOL.'Lvl '.$technology->pivot->lvl.' '.$technology->name.' - '.TopUpdater::priceMerging($technology->getPrice($technology->pivot->number)).'Points ( '.$debugString.' )';   

                    $researchPoints += TopUpdater::priceMerging($technology->getPrice($technology->pivot->level));
                }
                $player->points_research = round($researchPoints/1000);
                $player->points_total += $player->points_research;

                $militaryPoint = 0;
                foreach($player->colonies[0]->units as $unit)
                {
                    $debugString = " ";
                    $debugPrice = $unit->getPrice($unit->pivot->number);
                    foreach ($debugPrice as $resource => $price)
                        $debugString .= $price.' '.$resource." ";
                    echo PHP_EOL.$unit->pivot->number.'x '.$unit->name.' - '.TopUpdater::priceMerging($unit->getPrice($unit->pivot->number)).'Points ( '.$debugString.' )';     

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