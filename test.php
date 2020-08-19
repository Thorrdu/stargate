<?php

//DiscordPHP
include __DIR__.'/vendor/autoload.php';

//Laravel
require __DIR__.'/laravel/vendor/autoload.php';
$app = require_once __DIR__.'/laravel/bootstrap/app.php';

$app->make('Illuminate\Contracts\Http\Kernel')
    ->handle(Illuminate\Http\Request::capture());

use App\Building;
use App\Player;
use App\Colony;
use App\Technology;
use App\Defence;
use Faker\Factory as Faker;
use App\Utility\TopUpdater;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;
/*
$newLimit = round(DB::table('players')->Where([['npc',0],['id','!=',1],['points_total','>',0]])->avg('points_total'));

Config::set('stargate.gateFight.StrongWeak', $newLimit);

echo config('stargate.gateFight.StrongWeak');
die();*/

$building = Building::find(10); //17 fer 18 or

for($cpt = 1; $cpt < 15; $cpt++)
{
	//print_r($building->getPrice($cpt));
	echo PHP_EOL.'Lvl '.$cpt.' '.round($building->getConsumption($cpt)).' | '.round($building->getProductionE2PZ($cpt));
}
die();
/*
$buildings = Defence::all();
foreach($buildings as $buidling)
{
	echo PHP_EOL."'$buidling->slug' => [
			'name' => \"{$buidling->name}\",
			'description' => \"{$buidling->description}\",
	],";
} 
die();*/
/*
$building = Building::find(19); //17 fer 18 or

for($cpt = 1; $cpt < 15; $cpt++)
{
	print_r($building->getPrice($cpt));
	echo PHP_EOL.'Lvl '.$cpt.' '.round($building->getConsumption($cpt)).' | '.round($building->getProductionE2PZ($cpt));
}*/
/*
$players = Player::all();
foreach ($players as $player) {
	$player->active_colony_id = $player->colonies[0]->id;
	$player->save();
}
/*

$newPlayer = new Player;
$newPlayer->user_id = 125641223544373248;
$newPlayer->user_name = 'Thorrdu';
$newPlayer->ban = false;
$newPlayer->votes = 0;
$newPlayer->save();

echo $newPlayer->id;

$newPlayer->addColony();*/

//$post = Player::find(1);
//$playerByDiscord = Player::where('user_id', 125641223544373248);
/*
try{
	$player = Player::where('user_id', 125641223544373248)->firstOrFail();
	$buildingToBuild = Building::find(2);
	$player->activeColony->startBuilding($buildingToBuild);
}
catch(\Exception $e)
{
	echo $e->getMessage();
}*/

/*
try{
	$player = Player::where('user_id', 125641223544373248)->firstOrFail();
	//print_r($player->activeColony->buildings[0]->attributesToArray());

	foreach($player->activeColony->buildings as $building)
	{
		echo PHP_EOL.$building->building->name.' --> '.$building->level;
	}
	echo PHP_EOL.$player->activeColony->active_building_end;
}
catch(\Exception $e)
{
	echo $e->getMessage();
}
$date = new DateTime();
$timeZone = $date->getTimezone();
echo $timeZone->getName();
echo PHP_EOL.date("H:i:s");*/

/*
$building = Building::find(11); //17 fer 18 or

for($cpt = 1; $cpt < 15; $cpt++)
{
	print_r($building->getPrice($cpt));
	echo PHP_EOL.'Lvl '.$cpt.' '.round($building->getConsumption($cpt)).' | '.round($building->getProduction($cpt));
	//finished: 3 lvl 5
	// le 12 sera débloqué
}*/
//['colony_id' => $this->id, 'building_id' => $building->id]
try{
/*
	$player = Player::find(1);
	$player->active_technology_id = 2;
	$player->save();
	$player->active_technology_id = null;
	$player->save();*/
	//$faker = Faker::create();


	$colonies = Colony::all();
	foreach($colonies as $colony)
	{
		$colony->calcProd();
		$colony->save();
		$colony->checkColony();
	}
	/*** 
	$arrayLocales = ['ar_EG', 'ar_PS', 'ar_SA', 'bg_BG', 'bs_BA', 'cs_CZ', 'de_DE', 'dk_DK', 'el_GR', 'en_AU', 'en_CA', 'en_GB', 'en_IN', 'en_NZ', 'en_US', 'es_ES', 'es_MX', 'et_EE', 'fa_IR', 'fi_FI', 'fr_FR', 'hi_IN', 'hr_HR', 'hu_HU', 'hy_AM', 'it_IT', 'ja_JP', 'ka_GE', 'ko_KR', 'lt_LT', 'lv_LV', 'ne_NP', 'nl_NL', 'no_NO', 'pl_PL', 'pt_BR', 'pt_PT', 'ro_RO', 'ru_RU', 'sl_SI', 'sv_SE', 'tr_TR', 'uk_UA', 'zh_CN', 'zh_TW'];
    	foreach (range(1,100) as $index) {

			$faker = Faker::create($arrayLocales[rand(0,count($arrayLocales)-1)]);

            echo PHP_EOL.'NPC '.$faker->firstname.' '.$faker->lastname;
			echo PHP_EOL.$faker->randomNumber(9).$faker->randomNumber(9);

			//$faker->numberBetween($min = 0, $max = 1)

            
	    }*/
}
catch(\Exception $e)
{
	echo PHP_EOL.$e->getMessage();

}
/*
try{
	$playerByDiscordost = Player::where('user_id', 125641223544373248)->firstOrFail();

	//print_r($playerByDiscordost->attributesToArray());
	//echo count($playerByDiscordost->colonies).' colonies';
	//print_r($playerByDiscordost->activeColony->attributesToArray());
	//var_dump($playerByDiscordost->activeColony->name);
	print_r($playerByDiscordost->activeColony->buildings[0]->attributesToArray());
	print_r($playerByDiscordost->activeColony->buildings[0]->building->attributesToArray());
	//var_dump($playerByDiscordost->activeColony->name);
}
catch(\Exception $e)
{
	$errorMessage = $e->getMessage();
	if(strstr($errorMessage,"No query results"))
		echo "Joueur non créé";
	else
		echo $errorMessage;
	//var_dump('aaa '.$e->getMessage());
}*/

/*
$player = Player::with('colonies')->where('user_id', 125641223544373248)->firstOrFail();
$player->votes = $player->votes+1;
$player->save();*/
/*
use Discord\Discord;

$discord = new Discord([
	'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',
]);

$discord->on('ready', function ($discord) {
	echo "Bot is ready!", PHP_EOL;

	// Listen for messages.
	$discord->on('message', function ($message, $discord) {
		echo "{$message->author->username}: {$message->content}",PHP_EOL;
	});
});

$discord->run();*/