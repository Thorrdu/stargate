<?php

namespace App\Commands;

use App\Player;

class Top extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return 'Vous êtes banni...';
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => 'Tops',
                "description" => 'Les meilleurs joueurs par catégories',
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];

            $generalString = "";
            $topGeneral = Player::all()->sortBy('points_total')->slice(0,9);
            foreach($topGeneral as $player)
                $generalString .= $player->user_name." - ".number_format($player->points_total)."\n";
            if(empty($generalString))
                $generalString = "/";
            $embed['fields'][] = [
                'name' => 'Général',
                'value' => $generalString,
                'inline' => true
                ];

            $buildingString = "";
            $topBuilding = Player::all()->sortBy('points_building')->slice(0,9);
            foreach($topBuilding as $player)
                $buildingString .= $player->user_name." - ".number_format($player->points_building)."\n";
            if(empty($buildingString))
                $buildingString = "/";
            $embed['fields'][] = [
                'name' => 'Bâtiments',
                'value' => $buildingString,
                'inline' => true
                ];      

            $researchString = "";
            $topResearch = Player::all()->sortBy('points_research')->slice(0,9);
            foreach($topResearch as $player)
                $researchString .= $player->user_name." - ".number_format($player->points_research)."\n";
            if(empty($researchString))
                $researchString = "/"; 
            $embed['fields'][] = [
                'name' => 'Recherche',
                'value' => $researchString,
                'inline' => true
                ]; 

            $militaryString = "";
            $topMilitary = Player::all()->sortBy('points_military')->slice(0,9);
            foreach($topMilitary as $player)
                $militaryString .= $player->user_name." - ".number_format($player->points_military)."\n";
            $militaryString = "";
            if(empty($militaryString))
                $militaryString = "/";
            $embed['fields'][] = [
                'name' => 'Militaire',
                'value' => $militaryString,
                'inline' => true
                ]; 

            $this->message->channel->sendMessage('', false, $embed);
            return;
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
    }
}
