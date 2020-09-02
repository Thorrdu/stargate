<?php

//DiscordPHP
include __DIR__.'/vendor/autoload.php';

//Laravel
require __DIR__.'/laravel/vendor/autoload.php';
$app = require_once __DIR__.'/laravel/bootstrap/app.php';

$app->make('Illuminate\Contracts\Http\Kernel')
    ->handle(Illuminate\Http\Request::capture());

/*
use App\Player;
$players = Player::all();
foreach ($players as $player) {
    echo 'aaaaaaaabbbbbbbb////';
    echo $player->name;
}*/

/*

$embed = [
            'image' => [
                'url' => 'http://web.thorr.ovh/point.jpg',
            ],
            'thumbnail' => [
                //'url' => 'http://web.thorr.ovh/point.jpg',
            ],
            //'color' => '#0099ff',
            'author' => [
                'name' => 'Le joueur',
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            //"title" => "",
            //"description" => "",
            'fields' =>array(
                '0' => array(
                    'name' => 'Fields',
                    'value' => 'They can have different fields with small headlines.',
                    'inline' => true
                ),
                '1' => array(
                    'name' => 'Fields',
                    'value' => 'You can put [masked links](http://google.com) inside of rich embeds.',
                    'inline' => true
                ),
                '2' => array(
                    'name' => 'Fields',
                    'value' => 'You can put [masked links](http://google.com) inside of rich embeds.',
                    'inline' => true
                ),
                '3' => array(
                    'name' => 'Fields',
                    'value' => 'You can put [masked links](http://google.com) inside of rich embeds.',
                    'inline' => false
                ),
                '4' => array(
                    'name' => 'Fields',
                    'value' => 'You can put [masked links](http://google.com) inside of rich embeds.',
                    'inline' => false
                ),
            ),
            'footer' => array(
                'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
                'text'  => 'Stargate',
            ),
        ];
 * 
 */

use App\Building;
use App\Player;
use App\Colony;
use App\Reminder;
use App\Exploration;
use App\GateFight;
use App\Alliance;
use Illuminate\Support\Str;

use App\Commands\{HelpCommand as CustomHelp, AllianceCommand, Start, Colony as ColonyCommand, Build, Refresh, Research, Invite, Vote, Ban, Profile, Top, Lang as LangCommand, Ping, Infos, Galaxy, Craft, Stargate, Reminder as ReminderCommand, Daily as DailyCommand, Hourly as HourlyCommand, DefenceCommand};
use App\Utility\TopUpdater;
 
//use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Parts\User\Game;
use Discord\Parts\Embed\Embed;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Discord\Parts\User\Activity;


global $upTimeStart;
$upTimeStart = Carbon::now();

$beta = false;
$token = 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c';
$prefix = '!';

if($beta)
{
    $token = 'NzQ1MDE1MzAwMTgwOTM0NzM2.XzrnkQ.77nbdwVfRZRYBsPCbIUaIs6YHfs';
    $prefix = '-';
}

$discord = new DiscordCommandClient([
	'token' => $token,
    'prefix' => $prefix,
    'defaultHelpCommand' => false,
    'discordOptions' => ['loadAllMembers' => true, 'pmChannels' => true]
]);

$discord->on('ready', function ($discord) use($beta){
    echo "Bot is starting up!", PHP_EOL;

    $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
        'name' => "!help | {$discord->guilds->count()} servers {$discord->users->count()} users",
        'type' => 3
    ]);
    $discord->updatePresence($activity);

    /*echo 'UPDATING PRESENCE'.PHP_EOL;
    $game = $discord->factory(Game::class, [
        'name' => "!help | {$discord->guilds->count()} servers {$discord->users->count()} users",
        'type' => 3,
    ]);
    $discord->updatePresence($game);*/
    //var_dump($discord);

    $newLimit = round(DB::table('players')->Where([['npc',0],['id','!=',1],['points_total','>',0]])->avg('points_total'));
    Config::set('stargate.gateFight.StrongWeak', $newLimit);
    echo PHP_EOL.'New Limit: '.config('stargate.gateFight.StrongWeak');

	// Listen for messages.
	$discord->on('message', function ($message) {
        if($message->author->user->bot == true)
            return;
        if($message->guild_id != 735390211130916904 && $message->guild_id != 735390211130916904)
            return;
		echo "{$message->author->user->username }: {$message->content}",PHP_EOL;
    });

    $discord->loop->addPeriodicTimer(360, function () use ($discord) {

        /*$activity = $discord->factory(\Discord\Parts\User\Activity::class, [
            'name' => "!help | {$discord->guilds->count()} servers {$discord->users->count()} users",
            'type' => 3
        ]);
        $discord->updatePresence($activity);*/

        $topRegen = DB::table('configuration')->Where([['key','top_regen'],['value','<',date("Y-m-d H:i:s")]])->count();
        if($topRegen == 1)
        {
            $players = Player::where(['npc' => 0])->get();
            foreach($players as $player)
                TopUpdater::update($player);

            $alliances = Alliance::All();
            foreach($alliances as $alliance)
                TopUpdater::updateAlliance($alliance);
            $newday = date("d")+1;
            if($newday<10)
                $newday = '0'.$newday;
            $nextTopRegen = date("Y-m-").($newday).' 00:00:00';
            $topRegen = DB::table('configuration')->Where([['key','top_regen']])->update(['value' => $nextTopRegen]);

        }

    });

    $discord->loop->addPeriodicTimer(200, function () use ($discord) {   
        $activity = $discord->factory(\Discord\Parts\User\Activity::class, [
            'name' => "!help | {$discord->guilds->count()} servers {$discord->users->count()} users",
            'type' => 3
        ]);
        $discord->updatePresence($activity);
    });

    $discord->loop->addPeriodicTimer(3600, function () use ($discord) {       
        $newLimit = round(DB::table('players')->Where([['npc',0],['id','!=',1],['points_total','>',0]])->avg('points_total'));
        Config::set('stargate.gateFight.StrongWeak', $newLimit);
        echo PHP_EOL.'New Limit: '.config('stargate.gateFight.StrongWeak');

        $activeFights = GateFight::Where('active',true)->get();
        $now = Carbon::now();
        foreach($activeFights as $activeFight)
        {
            $now = Carbon::now();     
            $fightTime = Carbon::createFromFormat("Y-m-d H:i:s",$activeFight->created_at);
            if($fightTime->diffInHours($now) > 72){
                $updatingFights = GateFight::Where([['active',true],['player_id_source',$activeFight->player_id_source],['player_id_dest',$activeFight->player_id_dest]])->get();
                foreach($updatingFights as $updatingFight)
                {
                    $updatingFight->active = false;
                    $updatingFight->save();
                }
            }
        }
        echo PHP_EOL.'END ONE HOUR CRON';
    });


    $discord->loop->addPeriodicTimer(60, function () use ($discord) {
        
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
        }

        $explorations = Exploration::where([['exploration_end', '<', $dateNow->format("Y-m-d H:i:s")],['exploration_result', null]])->get();
        echo PHP_EOL."CHECK EXPLORATIONS: ".$explorations->count();
        foreach($explorations as $exploration)
        {  
            $explorationOutcome = $exploration->outcome();
            $userExist = $discord->users->get('id',$exploration->player->user_id);
            if(!is_null($userExist))
            {
                $userExist->sendMessage($explorationOutcome);
            }
        }
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
        'cooldown' => 2
    ]);
    //trans('generic.missingRequirements', [], $this->player->lang)

    $discord->registerCommand('profile', function ($message, $args) use($discord){
        $command = new Profile($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.profile.description', [], 'fr'),
		'usage' => trans('help.profile.usage', [], 'fr'),
		'aliases' => array('p'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('colony', function ($message, $args) use($discord){
        $command = new ColonyCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.colony.description', [], 'fr'),
		'usage' => trans('help.colony.usage', [], 'fr'),
		'aliases' => array('c','co','col'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('craft', function ($message, $args) use($discord){
        $command = new Craft($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.craft.description', [], 'fr'),
		'usage' => trans('help.craft.usage', [], 'fr'),
		'aliases' => array('cr','cra','craf'),
        'cooldown' => 2
    ]);
    
    $discord->registerCommand('defence', function ($message, $args) use($discord){
        $command = new DefenceCommand($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.defence.description', [], 'fr'),
		'usage' => trans('help.defence.usage', [], 'fr'),
		'aliases' => array('d','de','def'),
        'cooldown' => 2
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

    $discord->registerCommand('stargate', function ($message, $args) use($discord){
        $command = new Stargate($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.stargate.description', [], 'fr'),
		'usage' => trans('help.stargate.usage', [], 'fr'),
		'aliases' => array('s','st','sta','star'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('build', function ($message, $args) use($discord) {
        $command = new Build($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.build.description', [], 'fr'),
		'usage' => trans('help.build.usage', [], 'fr'),
		'aliases' => array('b','bu'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('research', function ($message, $args) use($discord) {
        $command = new Research($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.research.description', [], 'fr'),
		'usage' => trans('help.research.usage', [], 'fr'),
		'aliases' => array('r','search'),
        'cooldown' => 2
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

    $discord->registerCommand('top', function ($message, $args) use($discord){
        $command = new Top($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.top.description', [], 'fr'),
		'usage' => trans('help.top.usage', [], 'fr'),
        //'aliases' => array('t')
        'cooldown' => 2

    ]);	

    $discord->registerCommand('invite', function ($message, $args) use($discord){
        $command = new Invite($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.invite.description', [], 'fr'),
		'usage' => trans('help.invite.usage', [], 'fr'),
		//'aliases' => array('r'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('daily', function ($message, $args) use($discord){
        try{
            $command = new DailyCommand($message,$args,$discord);
            return $command->execute();
        }catch(\Exception $e)
        {
            echo $e->getMessage();
        }
    },[
        'description' => trans('help.daily.description', [], 'fr'),
		'usage' => trans('help.daily.usage', [], 'fr'),
		'aliases' => array('da','day'),
        'cooldown' => 2
    ]);

    $discord->registerCommand('hourly', function ($message, $args) use($discord){
        try{
            $command = new HourlyCommand($message,$args,$discord);
            return $command->execute();
        }catch(\Exception $e)
        {
            echo $e->getMessage();
        }
        echo 'bb';
    },[
        'description' => trans('help.hourly.description', [], 'fr'),
		'usage' => trans('help.hourly.usage', [], 'fr'),
		'aliases' => array('ho','hr','hor'),
        'cooldown' => 2
    ]);

    $discord->registerCommand('vote', function ($message, $args) use($discord){
        $command = new Vote($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.vote.description', [], 'fr'),
		'usage' => trans('help.vote.usage', [], 'fr'),
		'aliases' => array('v','vo'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('lang', function ($message, $args) use($discord){
        $command = new LangCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.lang.description', [], 'fr'),
		'usage' => trans('help.lang.usage', [], 'fr'),
        //'aliases' => array('b')
        'cooldown' => 2

    ]);

    $discord->registerCommand('reminder', function ($message, $args) use($discord){
        $command = new ReminderCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.reminder.description', [], 'fr'),
		'usage' => trans('help.reminder.usage', [], 'fr'),
        'aliases' => array('rmd','rem','remind'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('ban', function ($message, $args) use($discord) {
        $command = new Ban($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.ban.description', [], 'fr'),
		'usage' => trans('help.ban.usage', [], 'fr'),
        //'aliases' => array('b')
        'cooldown' => 2
    ]);	

    $discord->registerCommand('infos', function ($message, $args) use($discord){
        $command = new Infos($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.infos.description', [], 'fr'),
		'usage' => trans('help.infos.usage', [], 'fr'),
        'aliases' => array('info'),
        'cooldown' => 2
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
            return $e->getMessage();
        }   
    },[
        'description' => trans('help.uptime.description', [], 'fr'),
		'usage' => trans('help.uptime.usage', [], 'fr'),
        'aliases' => array('up'),
        'cooldown' => 2
    ]);	

    $discord->registerCommand('ping', function ($message, $args) use($discord){
        $command = new Ping($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.ping.description', [], 'fr'),
		'usage' => trans('help.ping.usage', [], 'fr'),
        'cooldown' => 2
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
        echo $e->getMessage();
        });*/
    }

});

$discord->run();