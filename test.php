<?php

//DiscordPHP
include __DIR__.'/vendor/autoload.php';

//Laravel
require __DIR__.'/laravel/vendor/autoload.php';
$app = require_once __DIR__.'/laravel/bootstrap/app.php';

$app->make('Illuminate\Contracts\Http\Kernel')
    ->handle(Illuminate\Http\Request::capture());

use App\Alliance;
use App\Building;
use App\Player;
use App\Colony;
use App\Technology;
use App\Defence;
use App\Artifact;
use App\Coordinate;
use App\Fleet;
use App\ShipPart;
use App\Unit;
use App\Utility\FuncUtility;
use App\Utility\PlayerUtility;
use Faker\Factory as Faker;
use App\Utility\TopUpdater;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

die();
$players = Player::all();
foreach($players as $player)
{




	foreach($player->colonies as $colony)
	{

		$colony->checkColony();
		//$colony->save();
		echo PHP_EOL.$colony->id;
	}
}
die();

$gateFights = DB::table('gate_fights')
            ->groupBy('player_id_source')
            ->orderBy(DB::raw('count(player_id_source)', 'DESC'))
            ->get('*');
            //->lists('player_id_source');

echo '<pre>';
print_r($gateFights);
echo '</pre>';

die();

$forceUnit = array('shield' => 112539, 'shield_left' => 112539);

$damTaken = 0;
$damage = 43108;
for($phase = 1; $phase < 15 ; $phase++)
{

	if($forceUnit['shield_left'] < $forceUnit['shield'] && $phase > 1 && $phase < 12)
		$forceUnit['shield_left'] += (($forceUnit['shield']-$forceUnit['shield_left']) * 0.1) * (12-$phase);

	echo PHP_EOL;
	print_r($forceUnit);

	if($forceUnit['shield_left'] > 0)
	{
		if($forceUnit['shield_left'] < $damage)
			$damTaken = $forceUnit['shield_left'];
		else
			$damTaken = $damage;
	}
	else
		$damTaken = 0;

		echo PHP_EOL.'Passe: '.$phase.' '.$damTaken;
		
	$forceUnit['shield_left'] -= $damTaken;
}

die();
$try =                 $coordinate = Coordinate::leftJoin('colonies', function($join) {
	$join->on('coordinates.id', '=', 'colonies.coordinate_id');
  })
  ->whereNull('colonies.id')
  ->inRandomOrder()->limit(1)->first([
	'coordinates.*',
]);
var_dump($try);

echo PHP_EOL.$try->id;

die();

$a = 'eval(10)';
if(is_numeric($a))
	echo $a;
else
	echo 'no';



die();

$alliances = Alliance::All();
foreach($alliances as $alliance)
	TopUpdater::updateAlliance($alliance);
die();

$colony = Colony::find(1);

echo $colony->getArtifactBonus(['bonus_category' => 'Production', 'bonus_resource' => 'iron']);



die();
$fleetShips = [];
$fleetShips[] = array('id' => 10,'qty' => 11);
$fleetShips[] = array('id' => 1,'qty' => 5);

$found_key = array_search(1, array_column($fleetShips, 'id'));
if($found_key !== false)
echo 'FOUND: '.$found_key;
else
echo 'NOT FOUND: ';

die();

$player = Player::find(1);
$armamentTec = Technology::Where('slug', 'LIKE', 'armament')->first();
$armamentLvl = $player->hasTechnology($armamentTec);
$shieldTec = Technology::Where('slug', 'LIKE', 'shield')->first();
$shieldLvl = $player->hasTechnology($shieldTec);
$hullTec = Technology::Where('slug', 'LIKE', 'hull')->first();
$hullLvl = $player->hasTechnology($hullTec);

$firePower = 1;
if($armamentLvl)
	$firePower *= pow(1.1,$armamentLvl);
$shield = 1;
if($shieldLvl)
	$shield *= pow(1.1,$shieldLvl);
$hull = 1;
if($hullLvl)
	$hull *= pow(1.1,$hullLvl);


echo PHP_EOL.$firePower;
echo PHP_EOL.$shield;
echo PHP_EOL.$hull;


 


die();

$defenceForces = array();
$defenceForces = FuncUtility::array_orderby($defenceForces, 'fire_power', SORT_DESC, 'shield', SORT_DESC, 'hull', SORT_DESC); //'type', SORT_ASC,
print_r($defenceForces);
die();


$player = Player::find(1);
echo 'Bonus: '.$player->getShipSpeedBonus();

die();

$shiParts = ShipPart::all();
foreach($shiParts as $shipPart)
{
	echo PHP_EOL."'".$shipPart->slug."' => [\n".
        "'name' => \"".$shipPart->name."\"\n".
        "],";
}


die();


foreach(Player::find(1)->artifacts as $artifact)
	echo 'a';
/*
$fleet = Fleet::find(3);
$fleet->resolveFight();
die();*/

$building = Building::find(2);

for($cpt=1;$cpt<=20;$cpt++)
{
	$time = $building->getTime($cpt);

	$now = Carbon::now();
	$buildingEnd = Carbon::createFromFormat("Y-m-d H:i:s",Carbon::now()->add($time.'s'));
	$buildingTime = $now->diffForHumans($buildingEnd,[
		'parts' => 3,
		'short' => true, // short syntax as per current locale
		'syntax' => CarbonInterface::DIFF_ABSOLUTE
	]);
	echo "\n".$cpt.' - '.$buildingTime;
}

/*
function weighted_random_simple($values, $weights){ 
	$count = count($values); 
	$i = 0; 
	$n = 0; 
	$randWeights = [];
	foreach($values as $value)
		$randWeights[] = $weights[$value];
	$num = mt_rand(0, array_sum($randWeights)); 
	while($i < $count){
		$n += $randWeights[$i]; 
		if($n >= $num){
			break; 
		}
		$i++; 
	} 
	return $values[$i]; 
}

$categoryWeights = [
	'Production' => 30,
	'Time' => 20,
	'Price' => 20,
	'DefenceLure' => 10,
	'ColonyMax' => 10
];


$bonusCategories = ['Production', 'Time', 'Price', 'DefenceLure','ColonyMax'];

for($cpt=0;$cpt<100;$cpt++)
echo PHP_EOL.weighted_random_simple($bonusCategories,$categoryWeights);
*/

/*
$player = Player::find(1);
$newArtifact = $player->activeColony->generateArtifact(array('maxEnding'=> 72));
echo $newArtifact->toString();
*/

/*


$json = file_get_contents('php://input');
if(isset($json) && !empty($json))
{
	$data = json_decode($json);
	if(!is_null($data->user))
	{

	}
}

*/





/*
$curl = curl_init();

switch ($method)
{
	case "POST":
		curl_setopt($curl, CURLOPT_POST, 1);

		if ($data)
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		break;
	case "PUT":
		curl_setopt($curl, CURLOPT_PUT, 1);
		break;
	default:
		if ($data)
			$url = sprintf("%s?%s", $url, http_build_query($data));
}

$url = 'https://top.gg/api/bots/{bot.id?}/votes';


curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjczMDgxNTM4ODQwMDYxNTQ1NSIsImJvdCI6dHJ1ZSwiaWF0IjoxNTk5MDcwNjczfQ.yfggNGkf534cT613lcBBOBMUBIpb30FAgvFB5lp8jJg");

curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$result = curl_exec($curl);

curl_close($curl);

return $result;
*/

die();




/*
$newLimit = round(DB::table('players')->Where([['npc',0],['id','!=',1],['points_total','>',0]])->avg('points_total'));

Config::set('stargate.gateFight.StrongWeak', $newLimit);

echo config('stargate.gateFight.StrongWeak');
die();*/
/*
$building = Building::find(10); //17 fer 18 or

for($cpt = 1; $cpt < 15; $cpt++)
{
	//print_r($building->getPrice($cpt));
	echo PHP_EOL.'Lvl '.$cpt.' '.round($building->getConsumption($cpt)).' | '.round($building->getProductionE2PZ($cpt));
}
die();*/
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
$newPlayer->user_id = config('stargate.ownerId');
$newPlayer->user_name = 'Thorrdu';
$newPlayer->ban = false;
$newPlayer->votes = 0;
$newPlayer->save();

echo $newPlayer->id;

$newPlayer->addColony();*/

//$post = Player::find(1);
//$playerByDiscord = Player::where('user_id', config('stargate.ownerId'));
/*
try{
	$player = Player::where('user_id', config('stargate.ownerId'))->firstOrFail();
	$buildingToBuild = Building::find(2);
	$player->activeColony->startBuilding($buildingToBuild);
}
catch(\Exception $e)
{
	echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
}*/

/*
try{
	$player = Player::where('user_id', config('stargate.ownerId'))->firstOrFail();
	//print_r($player->activeColony->buildings[0]->attributesToArray());

	foreach($player->activeColony->buildings as $building)
	{
		echo PHP_EOL.$building->building->name.' --> '.$building->level;
	}
	echo PHP_EOL.$player->activeColony->active_building_end;
}
catch(\Exception $e)
{
	echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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


	$players = Player::where('npc',1)->get();
	foreach($players as $player)
	{

		$defIds = [1,2,3];



		foreach($player->colonies as $colony)
		{
			$colony->calcProd();
			$colony->save();
			$colony->checkColony();

			$colony->military += rand(500,4000);
			$colony->save();
			
			foreach($defIds as $defId)
			{
				switch($defId)
				{
					default:
					case 1:
						$qty = rand(5,25);
					break;
					case 2:
						$qty = rand(2,10);
					break;
					case 3:
						$qty = rand(1,5);
					break;
				}
				///$colony->defences()->attach([$defId => ['number' => $qty]]);
			}
		}
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
	echo PHP_EOL.'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();

}
/*
try{
	$playerByDiscordost = Player::where('user_id', config('stargate.ownerId'))->firstOrFail();

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
	$errorMessage = 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
	if(strstr($errorMessage,"No query results"))
		echo "Joueur non créé";
	else
		echo $errorMessage;
	//var_dump('aaa '.'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage());
}*/

/*
$player = Player::with('colonies')->where('user_id', config('stargate.ownerId'))->firstOrFail();
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
		echo "{$message->author->user->username }: {$message->content}",PHP_EOL;
	});
});

$discord->run();*/