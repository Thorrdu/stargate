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

use App\Commands\{Start,Colony as ColonyCommand};

//use Discord\Discord;
use Discord\DiscordCommandClient;
use Discord\Parts\User\Game;
use Discord\Parts\Embed\Embed;


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

    $discord->registerCommand('start', function ($message, $args) {
        $startCommand = new Start($message,$args);
        return $startCommand->execute();
    },[
        'description' => config('stargate.commands.start.description'),
		'usage' => config('stargate.commands.start.usage'),
		'aliases' => array('s','start')
    ]);	

    $discord->registerCommand('colony', function ($message, $args) use ($discord) {
        $colonyCommand = new ColonyCommand($message,$args);
        return $colonyCommand->execute();
    },[
        'description' => 'Affiche les infos de votre colonie',
		'usage' => '`!colony`',
		'aliases' => array('c','co','col')
    ]);	


});

$discord->run();