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
use Illuminate\Support\Str;
/*
$players = Player::all();
foreach ($players as $player) {
    echo 'aaaaaaaabbbbbbbb////';
    echo $player->name;
}*/
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
	$player->colonies[0]->startBuilding($buildingToBuild);
}
catch(\Exception $e)
{
	echo $e->getMessage();
}*/
/*
try{
	$player = Player::where('user_id', 125641223544373248)->firstOrFail();
	//print_r($player->colonies[0]->buildings[0]->attributesToArray());

	foreach($player->colonies[0]->buildings as $building)
	{
		echo PHP_EOL.$building->building->name.' --> '.$building->level;
	}
	echo PHP_EOL.$player->colonies[0]->active_building_end;
}
catch(\Exception $e)
{
	echo $e->getMessage();
}
$date = new DateTime();
$timeZone = $date->getTimezone();
echo $timeZone->getName();
echo PHP_EOL.date("H:i:s");*/


$building = Building::find(5);
$coef = 1.2;
for($cpt = 1; $cpt < 15; $cpt++)
{
	echo PHP_EOL.'Lvl '.$cpt.' '.round($building->getEnergy($cpt)).' | '.round($building->getProductionRegular($cpt));
}
/*
try{
	$playerByDiscordost = Player::where('user_id', 125641223544373248)->firstOrFail();

	//print_r($playerByDiscordost->attributesToArray());
	//echo count($playerByDiscordost->colonies).' colonies';
	//print_r($playerByDiscordost->colonies[0]->attributesToArray());
	//var_dump($playerByDiscordost->colonies[0]->name);
	print_r($playerByDiscordost->colonies[0]->buildings[0]->attributesToArray());
	print_r($playerByDiscordost->colonies[0]->buildings[0]->building->attributesToArray());
	//var_dump($playerByDiscordost->colonies[0]->name);
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