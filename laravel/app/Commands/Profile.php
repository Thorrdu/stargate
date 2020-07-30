<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;

class Profile extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute profile';
            if($this->player->ban)
                return trans('generic.banned', [], $this->player->lang);

                $totalPlayers = DB::table('players')->count();
                $generalPosition = DB::table('players')->where('points_total', '>' , $this->player->points_total)->count() + 1;
                $buildingPosition = DB::table('players')->where('points_building', '>' , $this->player->points_building)->count() + 1;
                $researchPosition = DB::table('players')->where('points_research', '>' , $this->player->points_research)->count() + 1;
                $militaryPosition = DB::table('players')->where('points_military', '>' , $this->player->points_military)->count() + 1;

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => $this->player->user_name,
                "description" => "Lang: ".config('stargate.emotes.'.$this->player->lang)."\n"
                                ."Votes: ".$this->player->votes."\n",
                'fields' => [
                    [
                        'name' => 'Points',
                        'value' => trans('generic.general',[],$this->player->lang).": ".number_format($this->player->points_total)." Points (Position: ".number_format($generalPosition)."/{$totalPlayers})\n"
                                  .config('stargate.emotes.productionBuilding')." ".trans('generic.building',[],$this->player->lang).": Points ".number_format($this->player->points_building)." (".number_format($buildingPosition)."/{$totalPlayers})\n"
                                  .config('stargate.emotes.research')." ".trans('generic.research',[],$this->player->lang).": Points ".number_format($this->player->points_research)." (Position: ".number_format($researchPosition)."/{$totalPlayers})\n"
                                  .config('stargate.emotes.military')." ".trans('generic.military',[],$this->player->lang).": Points ".number_format($this->player->points_military)." (Position: ".number_format($militaryPosition)."/{$totalPlayers})\n",
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
                'name' => trans('generic.colonies',[],$this->player->lang),
                'value' => $coloniesString,
                'inline' => true
            ];

            $this->message->channel->sendMessage('', false, $embed);
            return ;
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
