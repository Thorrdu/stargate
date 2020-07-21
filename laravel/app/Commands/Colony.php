<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;

class Colony extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Colony';

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                //"title" => "",
                //"description" => "",
                'fields' =>array(
                    '0' => array(
                        'name' => 'Ressources',
                        'value' => 'Fer lalala',
                        'inline' => true
                    ),
                    '1' => array(
                        'name' => 'Production',
                        'value' => 'Fer lalala',
                        'inline' => true
                    ),
                    '2' => array(
                        'name' => 'Bâtiments de production',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),
                    '3' => array(
                        'name' => 'Bâtiments militaires',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),
                    '4' => array(
                        'name' => 'Bâtiments scientifiques',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),
                    '5' => array(
                        'name' => 'Bâtiments de stockage',
                        'value' => 'Mine lalala',
                        'inline' => true
                    ),

                ),
                'footer' => array(
                    'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png',
                    'text'  => 'Stargate',
                ),
            ];

            $this->message->channel->sendMessage('Colony Embed', false, $embed);
        }
        return false;
    }
}
