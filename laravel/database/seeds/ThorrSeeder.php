<?php

use Illuminate\Database\Seeder;
use App\Player;
use App\Building;
use App\Technology;
use App\Coordinate;

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
            1 => 20,
            2 => 20,//spy
            3 => 20,//counterSpy,
            4 => 20,//energy
            4 => 20,//energy
            5 => 20,//energy
            6 => 20,//energy
            7 => 20,//energy
            8 => 20,//energy
            9 => 20,//energy
            10 => 20,//energy
            11 => 20,//energy
            12 => 20,//energy
            13 => 20,//energy
            14 => 20,//energy
            15 => 20,//energy
            16 => 20,//energy
            17 => 20,//energy

        ];
        foreach($techToAdd as $techId => $techLevel)
            $thorrdu->technologies()->attach([$techId => ['level' => $techLevel]]);
        
        for($cpt = 2 ; $cpt <= config('stargate.galaxy.maxGalaxies') ; $cpt++)
        {
            $coordinate = Coordinate::where([['galaxy', $cpt],['system', 1],['planet', 1]])->first();
            $thorrdu->addColony($coordinate);
        }
        $thorrdu->load('colonies');

        $builToAdd = [
            /**Centrale et mines */
            1 => 20,
            2 => 20,
            3 => 20,
            4 => 20,
            5 => 20,

            /**Usine, labo, militaruy, chantier, defence*/
            6 => 20,
            7 => 20,
            8 => 20,
            9 => 20,
            10 => 20,
            11 => 20,
            12 => 20,
            13 => 20,
            14 => 20,
            15 => 20,
            16 => 20,
            17 => 20,
            18 => 20,
            19 => 20,
        ];

        foreach($thorrdu->colonies as $colony)
        {
            foreach($builToAdd as $builId => $builLevel)
            {
                $colony->buildings()->attach([$builId => ['level' => $builLevel]]);
            }
        
            foreach(config('stargate.resources') as $resource)
            {
                $colony->$resource = 100000;
            }
            $colony->military = 1000000000;
    
            $colony->save();
        }

    }
}
