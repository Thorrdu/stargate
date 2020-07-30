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

use App\Commands\{Start, Colony as ColonyCommand, Build, Refresh, Research, Invite, Vote, Ban, Profile, Top, Lang as LangCommand};
use App\Utility\TopUpdater;

//use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Parts\User\Game;
use Discord\Parts\Embed\Embed;
use Carbon\Carbon;


$discord = new DiscordCommandClient([
	'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',
	'prefix' => '!'
]);

$discord->on('ready', function ($discord) {
    echo "Bot is starting up!", PHP_EOL;
	echo 'UPDATING PRESENCE'.PHP_EOL;
    $game = $discord->factory(Game::class, [
        'name' => "!help | {$discord->users->count()} users",
        'type' => 3
    ]);
    $discord->updatePresence($game);

	// Listen for messages.
	$discord->on('message', function ($message) {
		echo "{$message->author->username}: {$message->content}",PHP_EOL;
    });
    
    
    $discord->loop->addPeriodicTimer(900, function () use ($discord) {
        $tenMinutes = Carbon::now()->add('minute', 15);
        $players = Player::where('last_top_update', '<', $tenMinutes->format("Y-m-d H:i:s"))->get();
        foreach($players as $player)
            TopUpdater::update($player);
    });

    $discord->registerCommand('start', function ($message, $args) use($discord){
        $command = new Start($message,$args,$discord);
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

    $discord->registerCommand('build', function ($message, $args) use($discord) {
        $command = new Build($message,$args,$discord);
        return $command->execute();
    },[
        'description' => 'Liste ou construit un bâtiment',
		'usage' => "`!build list`\n`!build [Numéro]`",
		'aliases' => array('b','bu'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('research', function ($message, $args) use($discord) {
        $command = new Research($message,$args,$discord);
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

    $discord->registerCommand('top', function ($message, $args) {
        $command = new Top($message,$args);
        return $command->execute();
    },[
        'description' => 'Affiche les divers Tops',
		'usage' => "`!top`",
		//'aliases' => array('t')
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
		'aliases' => array('v','vo'),
        'cooldown' => 5
    ]);	

    $discord->registerCommand('lang', function ($message, $args) use($discord) {
        $command = new LangCommand($message,$args,$discord);
        return $command->execute();
    },[
        'description' => 'Banni un joueur du bot.',
		'usage' => "`!ban @mention`",
		//'aliases' => array('b')
    ]);

    $discord->registerCommand('ban', function ($message, $args) {
        $command = new Ban($message,$args);
        return $command->execute();
    },[
        'description' => 'Banni un joueur du bot.',
		'usage' => "`!ban @mention`",
		//'aliases' => array('b')
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
        'cooldown' => 5
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