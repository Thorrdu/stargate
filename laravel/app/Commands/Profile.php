<?php

namespace App\Commands;

use App\Player;

class Profile extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute profile';
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => 'Profile de '.$this->player->user_name,
                "description" => 'Votes: '.$this->player->votes,
                'fields' => [
                    [
                        'name' => 'Points',
                        'value' => "Total: ".number_format($this->player->points_total)." points\nBâtiments: ".number_format($this->player->points_building)." Points\nRecherches: ".number_format($this->player->points_research)." Points\nMilitaire: ".number_format($this->player->points_military)." Points",
                        'inline' => true
                    ]
                ],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];

            $coloniesString = "";
            foreach($this->player->colonies as $colony)
            {
                $coloniesString .= $colony->name."\n";
            }
            $embed['fields'][] = [
                'name' => 'Colonies',
                'value' => $coloniesString,
                'inline' => true
            ];

            $this->message->channel->sendMessage('', false, $embed);
            return ;
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
    }
}
