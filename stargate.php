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

//use Discord\Discord;
use Discord\DiscordCommandClient;

$discord = new DiscordCommandClient([
	'token' => 'NzMwODE1Mzg4NDAwNjE1NDU1.Xwc_Dg.9GJ5Mww-YtAeQZZ-2C9MR3EWn2c',
	'prefix' => '!'
]);

$discord->on('ready', function ($discord) {
	echo "Bot is ready!", PHP_EOL;

	// Listen for messages.
	$discord->on('message', function ($message, $discord) {
		echo "{$message->author->username}: {$message->content}",PHP_EOL;
	});

    $discord->registerCommand('test', function ($message) use ($discord) {

        var_dump($discord->options);
        $message->channel->sendMessage('test')->then(
            function ($response) /*use ($deferred)*/ {
                $response->react('🥓');
            }
        );
    },[
        'description' => 'A basic test command',
		'usage' => "!test",
		'aliases' => array('t','tes')
    ]);
    

/*
		, [
			'description' => 'Provides a list of commands available.',
			'usage'       => '[command]',
		]);
*/
	
});

$discord->run();