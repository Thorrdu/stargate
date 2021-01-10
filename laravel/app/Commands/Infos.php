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

        if(!is_null($this->player) && $this->player->ban)
            return trans('generic.banned',[],$this->player->lang);

        $totalServer = number_format(DB::table('configuration')->Where([['key','LIKE','shardServer%']])->sum('value'));
        //$totalUsers = number_format(DB::table('configuration')->Where([['key','LIKE','shardUser%']])->sum('value'));
        $shardDisplay = $this->discord->commandClientOptions['discordOptions']['shardId'];

        $totalPlayers = DB::table('players')->where('npc', 0)->count();
        $embed = [
            'author' => [
                'name' => "Stargate",
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => "Infos",
            "description" => trans('infos.description', [], 'en'),
            'fields' => [
                [
                    'name' => 'Author',
                    'value' => 'Thorrdu#3117',
                    'inline' => true
                ],
                [
                    'name' => 'Version',
                    'value' => config('stargate.version'),
                    'inline' => true
                ],
                [
                    'name' => 'Library',
                    'value' => 'rocketmates/discord-php',
                    'inline' => true
                ],
                [
                    'name' => 'Shards',
                    'value' => "{($shardDisplay+1)}/{$this->discord->commandClientOptions['discordOptions']['shardCount']}",
                    'inline' => true
                ],
                [
                    'name' => 'Servers',
                    'value' => $totalServer,
                    'inline' => true
                ],
                /*[
                    'name' => 'Users',
                    'value' => $totalUsers,
                    'inline' => true
                ],*/
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
