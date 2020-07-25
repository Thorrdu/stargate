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
use Illuminate\Support\Str;

use App\Commands\{Start, Colony as ColonyCommand, Build, Refresh, Research, Invite, Vote, Ban, Profile};

//use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Parts\User\Game;
use Discord\Parts\Embed\Embed;


$discord = new DiscordCommandClient([
	'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',
	'prefix' => '!'
]);

$discord->on('ready', function ($discord) {
	echo "Bot is starting up!", PHP_EOL;
	echo 'UPDATING PRESENCE'.PHP_EOL;
    try
    {
        $game = $discord->factory(Game::class, [
            'name' => "!help | {$discord->users->count()} users",
            'type' => 3
        ]);
        $discord->updatePresence($game);
    }
    catch(\Exception $e)
    {
        echo $e->getMessage();
    }   

	// Listen for messages.
	$discord->on('message', function ($message) {
		echo "{$message->author->username}: {$message->content}",PHP_EOL;
	});

  /*  
    $discord->registerCommand('await', function ($message, $args) use ($discord){
        
        echo 'PRE';
        global $count;
        $count = 0;
        $countMax = 2;
        $timeMax = time();
        $message->channel->sendMessage("LISTEN ON");

        try{
            $discord->on('message', function ($messageListen,$response) use ($message,$countMax,$timeMax){
                global $count;
                if($message->channel->id == $messageListen->channel->id && $message->author->id == $messageListen->author->id)
                {
                    $messageListen->channel->sendMessage("LISTEN: {$count}/{$countMax} {$messageListen->author->username}: {$messageListen->content}");
                    $count++;
                }
                
                try{
                    if($countMax <= $count)
                    {

                        
                        throw 'aaa';
                    }
                }
                catch(\Exception $e)
                {
                    $message->channel->sendMessage($e->getMessage());
                }  
            });
        }
        catch(\Exception $e)
        {
            $message->channel->sendMessage($e->getMessage());
        }       
        finally
        {
            $message->channel->sendMessage("LISTEN OFF");

        }
    },[
        'description' => 'test',
        'usage' => 'test',
        'aliases' => array('ttt'),

        //'aliases' => array('t'),
    ]);*/


    $discord->registerCommand('test', function ($message, $args) {
        return 'test received';
    },[
        'description' => 'test',
		'usage' => 'test',
        'aliases' => array('t'),
        'cooldown' => 5

    ]);	
    
    $discord->registerCommand('start', function ($message, $args) {
        $command = new Start($message,$args);
        return $command->execute();
    },[
        'description' => config('stargate.commands.start.description'),
		'usage' => config('stargate.commands.start.usage'),
        'aliases' => array('s','start'),
        'cooldown' => 5
    ]);

    $discord->registerCommand('profile', function ($message, $args) {
        $command = new Profile($message,$args);
        return $command->execute();
    },[
        'description' => 'Affiche le profile',
		'usage' => "`!profile`",
		'aliases' => array('p'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('colony', function ($message, $args) {
        $command = new ColonyCommand($message,$args);
        return $command->execute();
    },[
        'description' => 'Affiche les infos de votre colonie',
		'usage' => '`!colony`',
		'aliases' => array('c','co','col'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('build', function ($message, $args) {
        $command = new Build($message,$args);
        return $command->execute();
    },[
        'description' => 'Liste ou construit un bâtiment',
		'usage' => "`!build list`\n`!build [Numéro]`",
		'aliases' => array('b','bu'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('research', function ($message, $args) {
        $command = new Research($message,$args);
        return $command->execute();
    },[
        'description' => 'Liste ou recherche une technologie',
		'usage' => "`!research list`\n`!research [Numéro]`",
		'aliases' => array('r','search'),
        'cooldown' => 5
    ]);

    $discord->registerCommand('refresh', function ($message, $args) {
        $command = new Refresh($message,$args);
        return $command->execute();
    },[
        'description' => 'Force Prod Refresh',
		'usage' => "`!refresh`",
		//'aliases' => array('r'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('invite', function ($message, $args) {
        $command = new Invite($message,$args);
        return $command->execute();
    },[
        'description' => 'Get invite link',
		'usage' => "`!invite`",
		//'aliases' => array('r'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('vote', function ($message, $args) {
        $command = new Vote($message,$args);
        return $command->execute();
    },[
        'description' => 'Get vote link',
		'usage' => "`!vote`",
		//'aliases' => array('r'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('ban', function ($message, $args) {
        $command = new Ban($message,$args);
        return $command->execute();
    },[
        'description' => 'Banni un joueur du bot.',
		'usage' => "`!ban @mention`",
		'aliases' => array('b')
    ]);	

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