<?php
//DiscordPHP
include __DIR__.'/vendor/autoload.php';

//Laravel
require __DIR__.'/laravel/vendor/autoload.php';
/*
require __DIR__.'/CustomCommandClient.php';
require __DIR__.'/Command.php';*/

$app = require_once __DIR__.'/laravel/bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')
    ->handle(Illuminate\Http\Request::capture());


use App\Building;
use App\Player;
use App\Colony;
use App\Reminder;
use App\Exploration;
use App\Alliance;
use App\Artifact;
use Illuminate\Support\Str;

use App\Commands\{HelpCommand as CustomHelp, Captcha, Premium, AllianceCommand, TradeCommand, Start, Empire, Colony as ColonyCommand, Build, Refresh, Research, Invite, Vote, Ban, Profile, Top, Lang as LangCommand, Ping, Infos, Galaxy, Craft, Stargate, Shipyard, Reminder as ReminderCommand, Daily as DailyCommand, Hourly as HourlyCommand, DefenceCommand, FleetCommand};
use App\Fleet;
use App\Trade;
use App\Utility\TopUpdater;
 
//use Discord\Discord;
use Discord\DiscordCommandClient;
//use Discord\CustomCommandClient;

use Discord\Parts\User\Game;
use Discord\Parts\Embed\Embed;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Discord\Parts\User\Activity;


global $upTimeStart;
$upTimeStart = Carbon::now();

global $beta;
$beta = false;
if(basename($_SERVER['PHP_SELF']) == "beta_stargate.php")
    $beta = true;
$token = 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc-3g.Sc1wU-YOokbAS2HXVc8sNt_R02w';
$prefix = '!';

if($beta)
{
    $token = 'NzQ1MDE1MzAwMTgwOTM0NzM2.XzrnkQ.77nbdwVfRZRYBsPCbIUaIs6YHfs';
    $prefix = '-';
}

$discorOpt = ['loadAllMembers' => true, 'pmChannels' => true];

if(isset($argv) && count($argv) > 2)
{
    $discorOpt = ['loadAllMembers' => true, 'pmChannels' => true, 'shardId' => $argv[1], 'shardCount' => $argv[2]];
}
else
{
    $discorOpt = ['loadAllMembers' => true, 'pmChannels' => true, 'shardId' => 0, 'shardCount' => 1];
}

$discord = new DiscordCommandClient([
	'token' => $token,
    'prefix' => $prefix,
    'defaultHelpCommand' => false,
    'discordOptions' => $discorOpt
]);

$discord->on('ready', function ($discord) use($beta){
    echo "Bot is starting upp!", PHP_EOL;

    
    echo 'UPDATING PRESENCE'.PHP_EOL;
    $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
        'name' => "Shard {$discord->commandClientOptions['discordOptions']['shardId']}/{$discord->commandClientOptions['discordOptions']['shardCount']} loading...",
        'type' => 3
    ]);
    $discord->updatePresence($activity);

    try{
        $rowExists = DB::table('configuration')->Where([['key','LIKE','shardServer'.$discord->commandClientOptions['discordOptions']['shardId']]])->count();
        if($rowExists == 0)
        {
            $usrCount = $discord->users->count();
            if($discord->commandClientOptions['discordOptions']['shardId'] == 0 && !$beta)
                $usrCount += 135000;

            DB::table('configuration')->insert([
                'key' => 'shardServer'.$discord->commandClientOptions['discordOptions']['shardId'],
                'value' => $discord->guilds->count(),
            ]);
            DB::table('configuration')->insert([
                'key' => 'shardUser'.$discord->commandClientOptions['discordOptions']['shardId'],
                'value' => $usrCount,
            ]);
        }
        else
        {
            DB::table('configuration')->Where([['key','LIKE','shardServer'.$discord->commandClientOptions['discordOptions']['shardId']]])->update(['value' => $discord->guilds->count()]);
            DB::table('configuration')->Where([['key','LIKE','shardUser'.$discord->commandClientOptions['discordOptions']['shardId']]])->update(['value' => $discord->users->count()]);
        }
    }
    catch(\Exception $e)
    {
        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
    }
    
    $newLimit = ceil(DB::table('players')->Where([['npc',0],['id','!=',1],['points_total','>',0]])->avg('points_total')/2);
    Config::set('stargate.gateFight.StrongWeak', $newLimit);
    echo PHP_EOL.'New Limit: '.config('stargate.gateFight.StrongWeak');

	// Listen for messages.
	/*$discord->on('message', function ($message) {
        if($message->author->user->bot == true)
            return;
        if($message->guild_id != 735390211130916904 && $message->guild_id != 735390211130916904)
            return;
		echo "{$message->author->user->username }: {$message->content}",PHP_EOL;
    });*/

    if($discord->commandClientOptions['discordOptions']['shardId'] == 0)
    {
        $discord->loop->addPeriodicTimer(45, function () use ($discord) {
            $dateNow = Carbon::now();
            $endedFleets = Fleet::where([['arrival_date', '<', $dateNow->format("Y-m-d H:i:s")], ['ended', false]])->get();
            echo PHP_EOL."CHECK FLEETS: ".$endedFleets->count();
            foreach($endedFleets as $endedFleet)
            {  
                $endedFleet->outcome();
            }

            $stargateBurials = Colony::where([['stargate_action_date', '<', $dateNow->format("Y-m-d H:i:s")], ['stargate_burying', true]])->get();
            foreach($stargateBurials as $stargateBurial)
            {
                $stargateBurial->stargate_buried = !$stargateBurial->stargate_buried;
                $stargateBurial->stargate_burying = false;
                $stargateBurial->stargate_action_date = null;
                $stargateBurial->save();
            }
        });

        $discord->loop->addPeriodicTimer(300, function () use ($discord) {

            $playersPremiumExpired = Player::Where([['premium_expiration','<>',''],['premium_expiration','<',date("Y-m-d H:i:s")]])->get();
            foreach($playersPremiumExpired as $player)
            {
                $player->premium_expiration = null;
                $player->save();
                foreach($player->colonies as $colony)
                {
                    $colony->calcProd(); //reload Prods
                    $colony->save(); 
                }
            }

            $playersTechnologyEnded = Player::Where('active_technology_end','<',date("Y-m-d H:i:s"))->get();
            foreach($playersTechnologyEnded as $playerTechnologyEnded)
            {
                $playerTechnologyEnded->checkTechnology();
            }

            $dateNow = Carbon::now();
            $explorations = Exploration::where([['exploration_end', '<', $dateNow->format("Y-m-d H:i:s")],['exploration_result', null]])->get();
            echo PHP_EOL."CHECK EXPLORATIONS: ".$explorations->count();
            foreach($explorations as $exploration)
            {  
                $explorationOutcome = $exploration->outcome();
                
                $reminder = new Reminder;
                $reminder->reminder_date = Carbon::now()->add('1s');
                $reminder->reminder = $explorationOutcome;
                $reminder->player_id = $exploration->player->id;
                $reminder->save();
            }

            $colonyCheckArtifacts = Colony::Where([['artifact_check','<>',''],['artifact_check','<',date("Y-m-d H:i:s")]])->get();
            echo PHP_EOL.''.$colonyCheckArtifacts->count().' colonies artifact to check' ;
            foreach($colonyCheckArtifacts as $colonyCheckArtifact)
            {
                $newArtifact = "";
                if($colonyCheckArtifact->player->colonies[0]->id == $colonyCheckArtifact->id)
                {
                    $newArtifact = $colonyCheckArtifact->generateArtifact(['forceBonus' => true])->toString($colonyCheckArtifact->player->lang);
                }
                elseif(rand(0,100) > 50)
                {
                    $newArtifact = $colonyCheckArtifact->generateArtifact()->toString($colonyCheckArtifact->player->lang);
                }
                $colonyCheckArtifact->refresh();
                $colonyCheckArtifact->artifact_check = null;
                $colonyCheckArtifact->save();

                if(!empty($newArtifact))
                {
                    $reminder = new Reminder;
                    $reminder->reminder_date = Carbon::now()->add(rand(1,5).'m');
                    $reminder->reminder = trans('colony.artifactDiscovered', ['artifact' => $newArtifact, 'planet' => $colonyCheckArtifact->name, 'coordinate' => $colonyCheckArtifact->coordinates->humanCoordinates()], $colonyCheckArtifact->player->lang);
                    $reminder->player_id = $colonyCheckArtifact->player->id;
                    $reminder->save();
                }
            }

            $topRegen = DB::table('configuration')->Where([['key','top_regen'],['value','<',date("Y-m-d H:i:s")]])->count();
            if($topRegen == 1)
            {
                echo PHP_EOL.'Top recalc';

                $players = Player::where(['npc' => 0])->get();
                foreach($players as $player)
                    TopUpdater::update($player);

                $alliances = Alliance::All();
                foreach($alliances as $alliance)
                    TopUpdater::updateAlliance($alliance);
                $newday = (int)date("d")+1;
                if($newday<10)
                    $newday = '0'.$newday;
                $nextTopRegen = date("Y-m-").($newday).' 00:00:00';
                $topRegen = DB::table('configuration')->Where([['key','top_regen']])->update(['value' => $nextTopRegen]);
            }

            $artifactAutoDeleted = 0;
            $artifactsToDelete = Artifact::Where([['bonus_end','<>',''],['bonus_end','<',date("Y-m-d H:i:s")]])->get();
            foreach($artifactsToDelete as $artifactDeletion)
            {
                if($artifactDeletion->bonus_category == 'Production')
                {
                    $colonyToRefresh = $artifactDeletion->colony;
                    $colonyToRefresh->checkProd();
                    $artifactDeletion->delete();
                    $colonyToRefresh->refresh();
                    $colonyToRefresh->calcProd();
                    $colonyToRefresh->save();
                }
                else
                    $artifactDeletion->delete();
                $artifactAutoDeleted++;
            }
            echo PHP_EOL.$artifactAutoDeleted.' Artefact deleted';
        });


        $discord->loop->addPeriodicTimer(3600, function () use ($discord) {

            echo PHP_EOL.'New Limit Calc';

            $newLimit = ceil(DB::table('players')->Where([['npc',0],['id','!=',1],['points_total','>',0]])->avg('points_total')/2);
            Config::set('stargate.gateFight.StrongWeak', $newLimit);
            echo PHP_EOL.'New Limit: '.config('stargate.gateFight.StrongWeak');

            $expiredTrades = Trade::Where([['active',true],['created_at', '<', Carbon::now()->sub('48h')]])->get();
            $now = Carbon::now();
            foreach($expiredTrades as $expiredTrade)
            {
                $now = Carbon::now();     
                $tradeTime = Carbon::createFromFormat("Y-m-d H:i:s",$expiredTrade->created_at);
                if($expiredTrade->getFairness())
                {
                    if(($expiredTrade->playerDest->trade_ban || $expiredTrade->playerSource->trade_ban))
                    {
                        //trade_ban Unban
                        $expiredTrade->playerSource->trade_ban = false;
                        $expiredTrade->playerDest->trade_extend = null;
                        $expiredTrade->playerSource->save();
                        $expiredTrade->playerDest->trade_ban = false;
                        $expiredTrade->playerDest->trade_extend = null;
                        $expiredTrade->playerDest->save();
                    }
                    $expiredTrade->active = false;
                    $expiredTrade->save();
                }
                else
                {
                    if(!$expiredTrade->warned){
                        $expiredTrade->warned = true;
                        $expiredTrade->save();

                        /**
                         * Envoi du warn
                         */    
                        
                        $reminderSource = new Reminder;
                        $reminderSource->reminder_date = Carbon::now()->add('1s');
                        $reminderSource->reminder = trans('trade.warn', ['tradeID' => $expiredTrade->id], $expiredTrade->playerSource->lang);
                        $reminderSource->player_id = $expiredTrade->playerSource->id;
                        $reminderSource->save();

                        $reminderDest = $reminderSource->replicate();
                        $reminderDest->reminder = trans('trade.warn', ['tradeID' => $expiredTrade->id], $expiredTrade->playerDest->lang);
                        $reminderDest->player_id = $expiredTrade->playerDest->id;
                        $reminderDest->save();

                    }
                    elseif(abs($tradeTime->diffInHours($now)) >= 72)
                    {
                        if(!$expiredTrade->extended && (!$expiredTrade->playerSource->trade_ban || !$expiredTrade->playerDest->trade_ban))
                        {
                            //trade_ban
                            $expiredTrade->playerSource->trade_ban = true;
                            $expiredTrade->playerSource->save();
                            $expiredTrade->playerDest->trade_ban = true;
                            $expiredTrade->playerDest->save();

                            /**
                             * Envoi du ban
                             */
                            $reminderSource = new Reminder;
                            $reminderSource->reminder_date = Carbon::now()->add('1s');
                            $reminderSource->reminder = trans('trade.ban', ['tradeID' => $expiredTrade->id], $expiredTrade->playerSource->lang);
                            $reminderSource->player_id = $expiredTrade->playerSource->id;
                            $reminderSource->save();

                            $reminderDest = $reminderSource->replicate();
                            $reminderDest->reminder = trans('trade.ban', ['tradeID' => $expiredTrade->id], $expiredTrade->playerDest->lang);
                            $reminderDest->player_id = $expiredTrade->playerDest->id;
                            $reminderDest->save();
                        }


                    }
                }
            }
        });
    }

    $discord->loop->addPeriodicTimer(30, function () use ($discord,$beta) {   

        $playersVoted = Player::Where('vote_flag',true)->get();
        foreach($playersVoted as $playerVoted)
        {
            $playerVoted->vote_flag = false;
            $playerVoted->save();
            
            $reminder = new Reminder;
            $reminder->reminder_date = Carbon::now()->add('1s');
            $reminder->reminder = trans('vote.thankyou', [], $playerVoted->lang);
            $reminder->player_id = $playerVoted->id;
            $reminder->save();
        }

        $totalServer = number_format(DB::table('configuration')->Where([['key','LIKE','shardServer%']])->sum('value'));
        $totalUsers = number_format(DB::table('configuration')->Where([['key','LIKE','shardUser%']])->sum('value'));

        $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
            'name' => "!help | {$totalServer} servers {$totalUsers} users",
            'type' => 3
        ]);
        $discord->updatePresence($activity);

        $usrCount = $discord->users->count();
        if($discord->commandClientOptions['discordOptions']['shardId'] == 0 && !$beta)
            $usrCount += 135000;

        DB::table('configuration')->Where([['key','LIKE','shardServer'.$discord->commandClientOptions['discordOptions']['shardId']]])->update(['value' => $discord->guilds->count()]);
        DB::table('configuration')->Where([['key','LIKE','shardUser'.$discord->commandClientOptions['discordOptions']['shardId']]])->update(['value' => $usrCount]);
        
            /*
        $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
            'name' => "!help | {$discord->guilds->count()} servers {$discord->users->count()} users",
            'type' => 3
        ]);
        $discord->updatePresence($activity);*/
    });



    $discord->loop->addPeriodicTimer(rand(40,60), function () use ($discord) {
        
        /*echo PHP_EOL.'UPDATING PRESENCE'.PHP_EOL;
        $game = $discord->factory(Game::class, [
            'name' => "!help | {$discord->guilds->count()} servers | {$discord->users->count()} users",
            'type' => 3
        ]);
        $discord->updatePresence($game);*/

        $dateNow = Carbon::now();
        $reminders = Reminder::where('reminder_date', '<', $dateNow->format("Y-m-d H:i:s"))->orderBy('player_id','asc')->get();
        $totalReminders = $reminders->count();
        echo PHP_EOL."CHECK REMINDER: {$totalReminders}";

        foreach($reminders as $reminder)
        {  
            if($reminder->player->npc == 1)
                $reminder->delete();
            else
            {
                $userExist = $discord->users->get('id',$reminder->player->user_id);
                if(!is_null($userExist))
                {
                    if(!is_null($reminder->embed))
                    {
                        $reminderEmbed = json_decode($reminder->embed,true);
                        $newEmbed = $discord->factory(Embed::class,$reminderEmbed);
                        $userExist->sendMessage('', false, $newEmbed);
                    }
                    else
                        $userExist->sendMessage($reminder->reminder);
                    $reminder->delete();
                }
            }
        }
        /*
        $playerIdRemind = 0;
        $rmdCounter = 0;
        $rmdMessagesStr = "";
        foreach($reminders as $reminder)
        {  
            $rmdCounter++;
            
            if($playerIdRemind == 0)
                $playerIdRemind = $reminder->player->user_id;
            
            if($totalReminders == $rmdCounter || $playerIdRemind != $reminder->player->user_id)
            {
                if($totalReminders == $rmdCounter)
                {
                    if($playerIdRemind == $reminder->player->user_id)
                    {
                        $rmdMessagesStr .= $reminder->reminder;

                        $userExist = $discord->users->get('id',$playerIdRemind);
                        if(!is_null($userExist))
                            $userExist->sendMessage($rmdMessagesStr);
                    }
                    else
                    {
                        $userExist = $discord->users->get('id',$playerIdRemind);
                        if(!is_null($userExist))
                            $userExist->sendMessage($rmdMessagesStr);

                        $playerIdRemind = $reminder->player->user_id;
                        $rmdMessagesStr = $reminder->reminder;

                        $userExist = $discord->users->get('id',$playerIdRemind);
                        if(!is_null($userExist))
                            $userExist->sendMessage($rmdMessagesStr);
                    }
                }
                else
                {
                    $userExist = $discord->users->get('id',$playerIdRemind);
                    if(!is_null($userExist))
                        $userExist->sendMessage($rmdMessagesStr);

                    $rmdMessagesStr = "";
                    $playerIdRemind = $reminder->player->user_id;
                }
            }
            $rmdMessagesStr .= $reminder->reminder."\n";

            $reminder->delete();
        }*/
    });
   
    $discord->registerCommand('help', function ($message, $args) use($discord){
        $command = new CustomHelp($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.start.description', [], 'fr'),
        'usage' => trans('help.start.usage', [], 'fr'),
        'aliases' => array('h','he'),
        'cooldown' => 2
    ]);

    $discord->registerCommand('start', function ($message, $args) use($discord){
        $command = new Start($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.start.description', [], 'fr'),
		'usage' => trans('help.start.usage', [], 'fr'),
        //'aliases' => array('start'),
        'cooldown' => 3
    ]);
    //trans('generic.missingRequirements', [], $this->player->lang)

    $discord->registerCommand('profile', function ($message, $args) use($discord){
        $command = new Profile($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.profile.description', [], 'fr'),
		'usage' => trans('help.profile.usage', [], 'fr'),
		'aliases' => array('p'),
        'cooldown' => 3
    ]);	

    $discord->registerCommand('colony', function ($message, $args) use($discord){
        $command = new ColonyCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.colony.description', [], 'fr'),
		'usage' => trans('help.colony.usage', [], 'fr'),
		'aliases' => array('c','co','col'),
        'cooldown' => 3
    ]);	

    $discord->registerCommand('build', function ($message, $args) use($discord) {
        $command = new Build($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.build.description', [], 'fr'),
		'usage' => trans('help.build.usage', [], 'fr'),
		'aliases' => array('b','bu'),
        'cooldown' => 3
    ]);	

    $discord->registerCommand('research', function ($message, $args) use($discord) {
        $command = new Research($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.research.description', [], 'fr'),
		'usage' => trans('help.research.usage', [], 'fr'),
		'aliases' => array('r','search'),
        'cooldown' => 3
    ]);
    
    $discord->registerCommand('craft', function ($message, $args) use($discord){
        $command = new Craft($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.craft.description', [], 'fr'),
		'usage' => trans('help.craft.usage', [], 'fr'),
		'aliases' => array('cr','cra','craf'),
        'cooldown' => 3
    ]);
    
    $discord->registerCommand('defence', function ($message, $args) use($discord){
        $command = new DefenceCommand($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.defence.description', [], 'fr'),
		'usage' => trans('help.defence.usage', [], 'fr'),
		'aliases' => array('d','de','def'),
        'cooldown' => 3
    ]);	

    $discord->registerCommand('empire', function ($message, $args) use($discord){
        $command = new Empire($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.empire.description', [], 'fr'),
		'usage' => trans('help.empire.usage', [], 'fr'),
		'aliases' => array('e','em','emp'),
        'cooldown' => 30
    ]);	

    $discord->registerCommand('premium', function ($message, $args) use($discord){
        $command = new Premium($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.premium.description', [], 'fr'),
		'usage' => trans('help.premium.usage', [], 'fr'),
		'aliases' => array('pre','prem'),
        'cooldown' => 5
    ]);	



    $discord->registerCommand('galaxy', function ($message, $args) use($discord){
        $command = new Galaxy($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.galaxy.description', [], 'fr'),
		'usage' => trans('help.galaxy.usage', [], 'fr'),
		'aliases' => array('g','ga','gal'),
        'cooldown' => 35
    ]);	

    $discord->registerCommand('fleet', function ($message, $args) use($discord){
        $command = new FleetCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.fleet.description', [], 'fr'),
		'usage' => trans('help.fleet.usage', [], 'fr'),
		'aliases' => array('fleet','f'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('shipyard', function ($message, $args) use($discord){
        $command = new Shipyard($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.shipyard.description', [], 'fr'),
		'usage' => trans('help.shipyard.usage', [], 'fr'),
		'aliases' => array('sh','ship'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('stargate', function ($message, $args) use($discord){
        $command = new Stargate($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.stargate.description', [], 'fr'),
		'usage' => trans('help.stargate.usage', [], 'fr'),
		'aliases' => array('s','st','sta','star'),
        'cooldown' => 5
    ]);

    /*
    $discord->registerCommand('refresh', function ($message, $args) use($discord){
        $command = new Refresh($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.refresh.description', [], 'fr'),
		'usage' => trans('help.refresh.usage', [], 'fr'),
		//'aliases' => array('r'),
        'cooldown' => 2
    ]);	*/

    $discord->registerCommand('alliance', function ($message, $args) use($discord){
        $command = new AllianceCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.alliance.description', [], 'fr'),
		'usage' => trans('help.alliance.usage', [], 'fr'),
        'aliases' => array('a','al','ally'),
        'cooldown' => 2

    ]);	

    $discord->registerCommand('trade', function ($message, $args) use($discord){
        $command = new TradeCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.trade.description', [], 'fr'),
		'usage' => trans('help.trade.usage', [], 'fr'),
        'aliases' => array('t','tr','tra'),
        'cooldown' => 5

    ]);	

    $discord->registerCommand('top', function ($message, $args) use($discord){
        $command = new Top($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.top.description', [], 'fr'),
		'usage' => trans('help.top.usage', [], 'fr'),
        //'aliases' => array('t')
        'cooldown' => 5

    ]);	

    $discord->registerCommand('invite', function ($message, $args) use($discord){
        $command = new Invite($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.invite.description', [], 'fr'),
		'usage' => trans('help.invite.usage', [], 'fr'),
		//'aliases' => array('r'),
        'cooldown' => 3
    ]);	

    $discord->registerCommand('vote', function ($message, $args) use($discord){
        $command = new Vote($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.vote.description', [], 'fr'),
		'usage' => trans('help.vote.usage', [], 'fr'),
		'aliases' => array('v','vo'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('daily', function ($message, $args) use($discord){
        try{
            $command = new DailyCommand($message,$args,$discord);
            return $command->execute();
        }catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    },[
        'description' => trans('help.daily.description', [], 'fr'),
		'usage' => trans('help.daily.usage', [], 'fr'),
		'aliases' => array('da','day'),
        'cooldown' => 5
    ]);

    $discord->registerCommand('hourly', function ($message, $args) use($discord){
        try{
            $command = new HourlyCommand($message,$args,$discord);
            return $command->execute();
        }catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    },[
        'description' => trans('help.hourly.description', [], 'fr'),
		'usage' => trans('help.hourly.usage', [], 'fr'),
		'aliases' => array('ho','hr','hor'),
        'cooldown' => 5
    ]);


    $discord->registerCommand('lang', function ($message, $args) use($discord){
        $command = new LangCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.lang.description', [], 'fr'),
		'usage' => trans('help.lang.usage', [], 'fr'),
        //'aliases' => array('b')
        'cooldown' => 5

    ]);

    $discord->registerCommand('reminder', function ($message, $args) use($discord){
        $command = new ReminderCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.reminder.description', [], 'fr'),
		'usage' => trans('help.reminder.usage', [], 'fr'),
        'aliases' => array('rmd','rem','remind'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('ban', function ($message, $args) use($discord) {
        $command = new Ban($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.ban.description', [], 'fr'),
		'usage' => trans('help.ban.usage', [], 'fr'),
        //'aliases' => array('b')
        'cooldown' => 5
    ]);	

    $discord->registerCommand('infos', function ($message, $args) use($discord){
        $command = new Infos($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.infos.description', [], 'fr'),
		'usage' => trans('help.infos.usage', [], 'fr'),
        'aliases' => array('info'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('uptime', function ($message, $args){
        global $upTimeStart;
        try{
            $now = Carbon::now();
            $upTime = $upTimeStart->diffForHumans($now,[
                'parts' => 3,
                'short' => true, // short syntax as per current locale
                'syntax' => CarbonInterface::DIFF_ABSOLUTE
            ]);
            //($upTimeStart);
            //$upTime = gmdate("H:i:s", $result);
            return 'Uptime : '.$upTime;
        }
        catch(\Exception $e)
        {
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }   
    },[
        'description' => trans('help.uptime.description', [], 'fr'),
		'usage' => trans('help.uptime.usage', [], 'fr'),
        'aliases' => array('up'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('captcha', function ($message, $args) use($discord){
        $command = new Captcha($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.captcha.description', [], 'fr'),
		'usage' => trans('help.captcha.usage', [], 'fr'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('ping', function ($message, $args) use($discord){
        $command = new Ping($message,$args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.ping.description', [], 'fr'),
		'usage' => trans('help.ping.usage', [], 'fr'),
        'cooldown' => 5
    ]);	

    /*
    $discord->registerCommand('test', function ($message, $args) use($discord) {
        $replyMess = "";
        foreach ($discord->guilds as $guild) {
            $replyMess .= "\n" . $guild->name." :: ".count($guild->members)." members";;
        }    
        echo $replyMess;

    },[
        'description' => 'Commande test à tout faire',
		'usage' => 'test',
        'aliases' => array('t'),
        'cooldown' => 2
    ]);	*/
    
    if(!$beta)
    {
        /*
        $mainGuild = $discord->guilds->get('id', 735390211130916904);
        $channelLogs = $mainGuild->channels->get('id', 735391076432478238);
        
        $channelLogs->sendMessage("Stargate just started")->then(function ($logMessage) {
            echo PHP_EOL.'Bot is ready';
        }, function ($e) {
        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        });*/
    }

});

$discord->run();