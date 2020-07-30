<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;

class Infos extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        echo PHP_EOL.'Execute Infos';
        try{

 

        $totalPlayers = DB::table('players')->count();
        $embed = [
            'author' => [
                'name' => "Stargate",
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
            ],
            "title" => "Stargate",
            "description" => trans('infos.description', [], 'en'),
            'fields' => [
                [
                    'name' => 'Author',
                    'value' => 'Thorrdu#3117',
                    'inline' => true
                ],
                [
                    'name' => 'Version',
                    'value' => '0.08 (Early Access)',
                    'inline' => true
                ],
                [
                    'name' => 'Uptime',
                    'value' => '?',
                    'inline' => true
                ],
                [
                    'name' => 'Library',
                    'value' => 'rocketmates/discord-php',
                    'inline' => true
                ],
                [
                    'name' => 'Servers',
                    'value' => number_format($this->discord->guilds->count()),
                    'inline' => true
                ],
                [
                    'name' => 'Users',
                    'value' => number_format($this->discord->users->count()),
                    'inline' => true
                ],
                [
                    'name' => 'Players',
                    'value' => number_format($totalPlayers),
                    'inline' => true
                ],
                [
                    'name' => 'Links',
                    'value' => "[Support Server](http://discord.gg/9hG6zaw)\n[Invite Stargate]https://discord.com/oauth2/authorize?client_id=730815388400615455&scope=bot&permissions=1047623",
                    'inline' => true
                ]
            ],
            'footer' => array(
                'text'  => 'Stargate',
            )
        ];

        var_dump($embed);
        $this->message->channel->sendMessage('', false, $embed);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
