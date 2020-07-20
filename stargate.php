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

use App\Building;
use App\Player;
use App\Colony;
use Illuminate\Support\Str;

use App\Commands\{Start};

//use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Parts\User\Game;

$discord = new DiscordCommandClient([
	'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',
	'prefix' => '!'
]);

$discord->on('ready', function ($discord) {
	echo "Bot is ready!", PHP_EOL;
	echo 'UPDATING PRESENCE'.PHP_EOL;
    try
    {
        $game = $discord->factory(Game::class, [
            'name' => "!help | Watching {$discord->users->count()} users",
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

    $discord->registerCommand('start', function ($message, $args) {
        $startCommand = new Start($message,$args);
        return $startCommand->execute();
    },[
        'description' => config('stargate.commands.start.description'),
		'usage' => config('stargate.commands.start.usage'),
		'aliases' => array('s','start')
    ]);	

    $discord->registerCommand('command2', function ($message, $args) {
        $message->channel->sendMessage('test')->then(
            function ($response) { //use ($deferred)
                $response->react('🥓');
            }
        );
        return;
    },[
        'description' => 'command2',
		'usage' => '`!command2`',
		'aliases' => array('c','cm2')
    ]);	


});

$discord->run();