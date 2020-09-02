<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Discord\Parts\Embed\Embed;

class Infos extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        echo PHP_EOL.'Execute Infos';

        $totalPlayers = DB::table('players')->count();
        $embed = [
            'author' => [
                'name' => "Stargate",
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
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
                    'value' => '0.4 (Early Access)',
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
                    'name' => 'Strong/Weak Limit',
                    'value' => number_format(config('stargate.gateFight.StrongWeak')),
                    'inline' => true
                ],
                [
                    'name' => 'Links',
                    'value' => "[Support Server](http://discord.gg/9hG6zaw)\n"
                              ."[Invitation](https://discordapp.com/oauth2/authorize?&client_id=730815388400615455&scope=bot&permissions=1047623)",
                    'inline' => false
                ]
            ],
            'footer' => array(
                'text'  => 'Stargate',
            )
        ];

        $newEmbed = $this->discord->factory(Embed::class,$embed);
        $this->message->channel->sendMessage('', false, $newEmbed);

    }
}
