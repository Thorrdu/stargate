<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Player;
use App\Building;
use App\Technology;

class NpcSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrayLocales = ['ar_EG', 'ar_PS', 'ar_SA', 'bg_BG', 'bs_BA', 'cs_CZ', 'de_DE', 'dk_DK', 'el_GR', 'en_AU', 'en_CA', 'en_GB', 'en_IN', 'en_NZ', 'en_US', 'es_ES', 'es_MX', 'et_EE', 'fa_IR', 'fi_FI', 'fr_FR', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM', 'it_IT', 'ja_JP', 'ka_GE', 'ko_KR', 'lt_LT', 'lv_LV', 'ne_NP', 'nl_NL', 'no_NO', 'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'sl_SI', 'sv_SE', 'tr_TR', 'uk_UA', 'zh_CN', 'zh_TW'];

        $wraith = ['proudbite', 'dimmaster', 'fall', 'grimflow', 'darkcopper', 'blankfront', 'guide', 'pureedge', 'despair', 'mud'];
        $tokRa = ['khu\'ri', 'su\'dop', 'agashak', 'arrim', 'khosnop', 'jerneec', 'geldoho', 'theldwal', 'zoldostu', 'kor\'suf'];
        $lucian = ['talvu', 'reg', 'tivrun', 'zushog', 'slulevuld', 'innizoz', 'slizifliold', 'tirvit', 'zig', 'nesatac'];
        $lantean = ['smundalmonn', 'tolihe', 'honrrumic', 'idregal', 'godalic', 'maveha', 'badrafolph', 'glenadenn', 'tsundun', 'iphiathe'];
        $jaffa = ['el\'cul', 'bil\'aa', 'sor\'y', 'i\'ra', 'vohron', 'nomug', 'dik\'nyr', 'ron\'taac', 'nolaag', 'ress\'od'];
        $goauld = ['masho', 'jish', 'budekit', 'su\'u', 'ucninek', 'cri', 'hapul', 'grec', 'utha', 'klamehesh'];
        $asgard = ['Isulf', 'Ragnar', 'Haki', 'Blann', 'Agmundr', 'Solvi', 'Hoskuld', 'Skurfa', 'Gavtvid', 'Grani'];
        $randomNames = array_merge($wraith,$tokRa,$lucian,$lantean,$jaffa,$goauld,$asgard);

        foreach ($randomNames as $randomName) {

			$faker = Faker::create($arrayLocales[rand(0,count($arrayLocales)-1)]);

            //echo PHP_EOL.preg_replace('/ \@.*/', '', $faker->email);
            //echo PHP_EOL.'NPC '.$faker->firstname.' '.$faker->lastname.' '.$faker->userName.' '.$faker->userAgent;

			//$faker->numberBetween($min = 0, $max = 1)
            $npc = new Player;
            $npc->user_id = $faker->randomNumber(9).$faker->randomNumber(9);
            $npc->user_name = ucfirst($randomName);
            $npc->ban = false;
            $npc->lang = 'fr';
            $npc->votes = 0;
            $npc->npc = true;
            $npc->save();
            $npc->addColony();

            $techToAdd = [
                /*1 => rand(3,5),
                2 => rand(3,6),//spy
                2 => rand(1,4),//counterSpy,
                4 => rand(3,5),//energy*/
            ];

            $builToAdd = [
                /**Centrale et mines */
                /* 1 => rand(13,15),
                2 => rand(9,13),
                3 => rand(8,12),
                4 => rand(4,7),
                5 => rand(3,5), */

                /**Usine, labo, militaruy, chantier, defence*/
                // 6 => 5,
                7 => 5,
                /* 8 => rand(4,8),
                9 => 5,
                15 => 5, */
            ];

            foreach($techToAdd as $techId => $techLevel)
                $npc->technologies()->attach([$techId => ['level' => $techLevel]]);

            foreach($builToAdd as $builId => $builLevel)
                $npc->activeColony->buildings()->attach([$builId => ['level' => $builLevel]]);

            foreach(config('stargate.resources') as $resource)
            {
                $npc->activeColony->$resource = 10000;
            }
            $npc->activeColony->military = 1000;

            $npc->activeColony->save();
	    }
	        /*
	            'name' => $faker->name,
	            'email' => $faker->email,
	            'password' => bcrypt('secret'),
            */



    }
}
