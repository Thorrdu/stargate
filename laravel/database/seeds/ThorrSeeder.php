<?php

use Illuminate\Database\Seeder;
use App\Player;
use App\Building;
use App\Technology;
use App\Coordinate;
use App\Alliance;
use App\AllianceRole;

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


        $alliance = new Alliance;
        $alliance->name = 'Stargate Command';
        $alliance->tag = 'SGC';
        $alliance->leader_id = 1;
        $alliance->founder_id = 1;
        $alliance->save();

        $role = new AllianceRole;
        $role->name = trans('alliance.defaultRoles.recruit', [], $thorrdu->lang);
        $role->right_level = 1;
        $role->right_recruit = false;
        $role->right_kick = false;
        $role->right_promote = false;
        $role->alliance_id = $alliance->id;
        $role->save();

        $role = new AllianceRole;
        $role->name = trans('alliance.defaultRoles.recruitOfficer', [], $thorrdu->lang);
        $role->right_level = 2;
        $role->right_recruit = true;
        $role->right_kick = false;
        $role->right_promote = false;
        $role->alliance_id = $alliance->id;
        $role->save();

        $role = new AllianceRole;
        $role->name = trans('alliance.defaultRoles.officer', [], $thorrdu->lang);
        $role->right_level = 3;
        $role->right_recruit = true;
        $role->right_kick = true;
        $role->right_promote = true;
        $role->alliance_id = $alliance->id;
        $role->save();

        $role = new AllianceRole;
        $role->name = trans('alliance.defaultRoles.council', [], $thorrdu->lang);
        $role->right_level = 4;
        $role->right_recruit = true;
        $role->right_kick = true;
        $role->right_promote = true;
        $role->alliance_id = $alliance->id;
        $role->save();

        $role = new AllianceRole;
        $role->name = trans('alliance.defaultRoles.leader', [], $thorrdu->lang);
        $role->right_level = 5;
        $role->right_recruit = true;
        $role->right_kick = true;
        $role->right_promote = true;
        $role->alliance_id = $alliance->id;
        $role->save();

        $thorrdu->alliance_id = $alliance->id;
        $thorrdu->role_id = $role->id;
        $thorrdu->save();

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
            11 => 10,//energy
            12 => 10,//energy
            13 => 10,//energy
            14 => 10,//energy
            15 => 10,//energy
            16 => 10,//energy
            17 => 10,//energy

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
            1 => 10,
            2 => 20,
            3 => 20,
            4 => 20,
            5 => 20,

            /**Usine, labo, militaruy, chantier, defence*/
            6 => 15,
            7 => 15,
            8 => 15,
            9 => 15,
            10 => 15,
            11 => 15,
            12 => 15,
            13 => 10,
            14 => 10,
            15 => 10,
            16 => 10,
            17 => 10,
            18 => 10,
            19 => 10,
        ];

        foreach($thorrdu->colonies as $colony)
        {
            foreach($builToAdd as $builId => $builLevel)
            {
                $colony->buildings()->attach([$builId => ['level' => $builLevel]]);
            }

            foreach(config('stargate.resources') as $resource)
            {
                $colony->$resource = 100000000;
            }
            $colony->military = 1000000000;

            $colony->save();
        }

    }
}
