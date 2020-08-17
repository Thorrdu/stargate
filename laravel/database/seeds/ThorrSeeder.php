<?php

use Illuminate\Database\Seeder;
use App\Player;
use App\Building;
use App\Technology;

class ThorrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $thorrdu = new Player;
        $thorrdu->user_id = 125641223544373248;
        $thorrdu->user_name = 'Thorrdu';
        $thorrdu->ban = false;
        $thorrdu->lang = 'fr';
        $thorrdu->votes = 0;
        $thorrdu->npc = false;
        $thorrdu->save();   
        $thorrdu->addColony();

        $techToAdd = [
            1 => 10,
            2 => 10,//spy
            3 => 10,//counterSpy,
            4 => 10,//energy
            4 => 10,//energy
            5 => 10,//energy
            6 => 10,//energy
            7 => 10,//energy
            8 => 10,//energy
            9 => 10,//energy
            10 => 10,//energy

        ];

        $builToAdd = [
            /**Centrale et mines */
            1 => 20,
            2 => 10,
            3 => 10,
            4 => 10,
            5 => 10,

            /**Usine, labo, militaruy, chantier, defence*/
            6 => 5,
            7 => 5,
            8 => 5,
            9 => 5,
            15 => 5,
        ];

        foreach($techToAdd as $techId => $techLevel)
            $thorrdu->technologies()->attach([$techId => ['level' => $techLevel]]);
        
        foreach($builToAdd as $builId => $builLevel)
        {
            $thorrdu->activeColony->buildings()->attach([$builId => ['level' => $builLevel]]);
        }
    
        foreach(config('stargate.resources') as $resource)
        {
            $thorrdu->activeColony->$resource = 100000;
        }
        $thorrdu->activeColony->military = 1000000000;

        $thorrdu->activeColony->save();
    }
}
