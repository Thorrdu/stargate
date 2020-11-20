<?php

use Illuminate\Database\Seeder;
use App\Player;
use App\Building;
use App\Technology;
use App\Coordinate;
use App\Alliance;
use App\AllianceRole;
use App\Defence;
use App\Unit;

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

        for($cpt = 2 ; $cpt <= config('stargate.galaxy.maxGalaxies') ; $cpt++)
        {
            $coordinate = Coordinate::where([['galaxy', $cpt],['system', 1],['planet', 1]])->first();
            $thorrdu->addColony($coordinate);
        }
        $thorrdu->load('colonies');



        $defenses = Defence::all();
        $units = Unit::all();
        $buildings = Building::all();
        $techs = Technology::all();

        foreach($techs as $tech)
        {
            $thorrdu->technologies()->attach([$tech->id => ['level' => 30]]);
        }

        foreach($thorrdu->colonies as $colony)
        {
            foreach($buildings as $building)
            {
                $colony->buildings()->attach([$building->id => ['level' => 30]]);
            }

            foreach($defenses as $defence)
            {
                $colony->defences()->attach([$defence->id => ['number' => 1000]]);
            }
            foreach($units as $unit)
            {
                $colony->units()->attach([$unit->id => ['number' => 10000]]);
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
