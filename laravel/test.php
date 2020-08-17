<?php
global $arrayCalc;
$arrayCalc = ['iron','gold','quartz','naqahdah'];

//DiscordPHP
//include __DIR__.'/vendor/autoload.php';

//Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make('Illuminate\Contracts\Http\Kernel')
    ->handle(Illuminate\Http\Request::capture());

use App\Building;
use App\Player;
use App\Colony;
use Illuminate\Support\Str;

use App\Commands\{Start};

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
	$player->activeColony->startBuilding($buildingToBuild);
}
catch(\Exception $e)
{
	echo $e->getMessage();
}*/


try{
	$startCommand = new Start( [] );
	echo PHP_EOL.$startCommand->help();
	echo PHP_EOL.$startCommand->execute();
	die();

	$player = Player::where('user_id', 125641223544373248)->firstOrFail();
	//print_r($player->activeColony->buildings[0]->attributesToArray());

	foreach($player->activeColony->buildings as $building)
	{
		echo PHP_EOL.trans('building.'.$building->slug.'.name', [], $this->player->lang).' --> '.$building->pivot->level;
	}
	echo PHP_EOL.'Prod Iron: '.$player->activeColony->production_iron;
	echo PHP_EOL.'Prod Gold: '.$player->activeColony->production_gold;
	echo PHP_EOL.'Prod Quartz: '.$player->activeColony->production_quartz;
	echo PHP_EOL.'Prod Naqahdah: '.$player->activeColony->production_naqahdah;

	if(!is_null($player->activeColony->active_building_end))
		echo PHP_EOL.'Building ends: '.$player->activeColony->active_building_end;
}
catch(\Exception $e)
{
	echo $e->getMessage();
}
$date = new DateTime();
$timeZone = $date->getTimezone();
echo $timeZone->getName();
echo PHP_EOL.date("H:i:s");
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