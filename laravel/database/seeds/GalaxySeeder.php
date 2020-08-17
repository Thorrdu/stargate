<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GalaxySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $maxGal = config('stargate.galaxy.maxGalaxies');
        $maxSys = config('stargate.galaxy.maxSystems');
        $maxPlanet = config('stargate.galaxy.maxPlanets');
        for($cptGal=1;$cptGal <= $maxGal;$cptGal++)
        {
            for($cptSys = 1; $cptSys <= $maxSys; $cptSys++)
            {
                $randPlanets = 10;//rand(3,$maxPlanet);
                for($cptPlanet = 1; $cptPlanet <= $randPlanets; $cptPlanet++)
                {
                    DB::table('coordinates')->insert([
                        'galaxy' => $cptGal,
                        'system' => $cptSys,
                        'planet' => $cptPlanet
                    ]);
                }
            }
        }

    }
}
