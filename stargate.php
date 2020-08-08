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
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
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
                'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                'text'  => 'Stargate',
            ),
        ];
 * 
 */

use App\Building;
use App\Player;
use App\Colony;
use App\Reminder;
use Illuminate\Support\Str;

use App\Commands\{Start, Colony as ColonyCommand, Build, Refresh, Research, Invite, Vote, Ban, Profile, Top, Lang as LangCommand, Ping, Infos, Galaxy, Craft, Stargate, Reminder as ReminderCommand};
use App\Utility\TopUpdater;
 
//use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Parts\User\Game;
use Discord\Parts\Embed\Embed;
use Carbon\Carbon;
use Carbon\CarbonInterface;

global $upTimeStart;
$upTimeStart = Carbon::now();

$discord = new DiscordCommandClient([
	'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',
	'prefix' => '!'
]);

$discord->on('ready', function ($discord) {
    echo "Bot is starting up!", PHP_EOL;
    echo 'UPDATING PRESENCE'.PHP_EOL;
    $game = $discord->factory(Game::class, [
        'name' => "!help | {$discord->guilds->count()} servers {$discord->users->count()} users",
        'type' => 3
    ]);
    $discord->updatePresence($game);

	// Listen for messages.
	$discord->on('message', function ($message) {
        if($message->guild_id != 735390211130916904 && $message->guild_id != 735390211130916904)
            return;
		echo "{$message->author->username}: {$message->content}",PHP_EOL;
    });

    
    $discord->loop->addPeriodicTimer(900, function () use ($discord) {
        $tenMinutes = Carbon::now()->sub('minute', 15);
        $players = Player::where('last_top_update', '<', $tenMinutes->format("Y-m-d H:i:s"))->get();
        foreach($players as $player)
            TopUpdater::update($player);
    });

    $discord->loop->addPeriodicTimer(60, function () use ($discord) {
        
        echo PHP_EOL.'UPDATING PRESENCE'.PHP_EOL;
        $game = $discord->factory(Game::class, [
            'name' => "!help | {$discord->guilds->count()} servers | {$discord->users->count()} users",
            'type' => 3
        ]);
        $discord->updatePresence($game);

        $dateNow = Carbon::now();
        $reminders = Reminder::where('reminder_date', '<', $dateNow->format("Y-m-d H:i:s"))->get();
        echo PHP_EOL."CHECK REMINDER: ".$reminders->count();
        foreach($reminders as $reminder)
        {  
            $userExist = $discord->users->filter(function ($value) use($reminder){
                return $value->id == $reminder->player->user_id;
            });
            if($userExist->count() > 0)
            {
                $foundUser = $userExist->first();
                $foundUser->sendMessage($reminder->reminder);
            }
            $reminder->delete();
        }
    });

    $discord->registerCommand('start', function ($message, $args) use($discord){
        $command = new Start($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.start.description', [], 'fr'),
		'usage' => trans('help.start.usage', [], 'fr'),
        //'aliases' => array('start'),
        'cooldown' => 4
    ]);
    //trans('generic.missingRequirements', [], $this->player->lang)

    $discord->registerCommand('profile', function ($message, $args) {
        $command = new Profile($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.profile.description', [], 'fr'),
		'usage' => trans('help.profile.usage', [], 'fr'),
		'aliases' => array('p'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('colony', function ($message, $args) {
        $command = new ColonyCommand($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.colony.description', [], 'fr'),
		'usage' => trans('help.colony.usage', [], 'fr'),
		'aliases' => array('c','co','col'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('craft', function ($message, $args) use($discord){
        $command = new Craft($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.craft.description', [], 'fr'),
		'usage' => trans('help.craft.usage', [], 'fr'),
		'aliases' => array('cr','cra','craf'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('galaxy', function ($message, $args) use($discord){
        $command = new Galaxy($message, $args, $discord);
        return $command->execute();
    },[
        'description' => trans('help.galaxy.description', [], 'fr'),
		'usage' => trans('help.galaxy.usage', [], 'fr'),
		'aliases' => array('g','ga','gal'),
        'cooldown' => 1
    ]);	

    $discord->registerCommand('stargate', function ($message, $args) {
        $command = new Stargate($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.stargate.description', [], 'fr'),
		'usage' => trans('help.stargate.usage', [], 'fr'),
		'aliases' => array('st','sta','star'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('build', function ($message, $args) use($discord) {
        $command = new Build($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.build.description', [], 'fr'),
		'usage' => trans('help.build.usage', [], 'fr'),
		'aliases' => array('b','bu'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('research', function ($message, $args) use($discord) {
        $command = new Research($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.research.description', [], 'fr'),
		'usage' => trans('help.research.usage', [], 'fr'),
		'aliases' => array('r','search'),
        'cooldown' => 4
    ]);

    $discord->registerCommand('refresh', function ($message, $args) {
        $command = new Refresh($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.refresh.description', [], 'fr'),
		'usage' => trans('help.refresh.usage', [], 'fr'),
		//'aliases' => array('r'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('top', function ($message, $args) use($discord){
        $command = new Top($message,$args,$discord);
        return $command->execute();
    },[
        'description' => trans('help.top.description', [], 'fr'),
		'usage' => trans('help.top.usage', [], 'fr'),
        //'aliases' => array('t')
        'cooldown' => 4

    ]);	

    $discord->registerCommand('invite', function ($message, $args) {
        $command = new Invite($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.invite.description', [], 'fr'),
		'usage' => trans('help.invite.usage', [], 'fr'),
		//'aliases' => array('r'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('vote', function ($message, $args) {
        $command = new Vote($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.vote.description', [], 'fr'),
		'usage' => trans('help.vote.usage', [], 'fr'),
		'aliases' => array('v','vo'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('lang', function ($message, $args){
        $command = new LangCommand($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.lang.description', [], 'fr'),
		'usage' => trans('help.lang.usage', [], 'fr'),
        //'aliases' => array('b')
        'cooldown' => 4

    ]);

    $discord->registerCommand('reminder', function ($message, $args){
        $command = new ReminderCommand($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.reminder.description', [], 'fr'),
		'usage' => trans('help.reminder.usage', [], 'fr'),
        'aliases' => array('re','rem','remind'),
        'cooldown' => 4
    ]);	

    $discord->registerCommand('ban', function ($message, $args) {
        $command = new Ban($message,$args);
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
        'cooldown' => 4
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
        'cooldown' => 4
    ]);	

    $discord->registerCommand('ping', function ($message, $args) {
        $command = new Ping($message,$args);
        return $command->execute();
    },[
        'description' => trans('help.ping.description', [], 'fr'),
		'usage' => trans('help.ping.usage', [], 'fr'),
        'cooldown' => 4
    ]);	
    /*
    $discord->registerCommand('test', function ($message, $args) use($discord) {
        return 'test received';
        $command = new Paginator($message,$args,$discord);
        return $command->execute();
    },[
        'description' => 'Commande test à tout faire',
		'usage' => 'test',
        'aliases' => array('t'),
        'cooldown' => 4
    ]);	*/
    

    $mainGuild = $discord->guilds->get('id', 735390211130916904);
    $channelLogs = $mainGuild->channels->get('id', 735391076432478238);
    
    /*
    $channelLogs->sendMessage("Stargate just started")->then(function ($logMessage) {
        echo PHP_EOL.'Bot is ready';
    }, function ($e) {
       echo $e->getMessage();
    });*/

});

$discord->run();