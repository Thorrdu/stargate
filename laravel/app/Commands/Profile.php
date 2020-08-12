<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Utility\TopUpdater;

class Profile extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute profile';
            if($this->player->ban)
                return trans('generic.banned', [], $this->player->lang);
                    
            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);


            if($this->player->user_id == 125641223544373248 && count($this->args) >= 1)
            {
                $player = Player::where('user_id', $this->args[0])->first();
                    TopUpdater::update($player);
                $this->player = $player;
            }

            if(!empty($this->args) && Str::startsWith('notification', $this->args[0]))
            {
                if(count($this->args) < 2)
                    return trans('profile.notification.missingParameter',[],$this->player->lang);
                if(Str::startsWith('on', $this->args[1]))
                {
                    $this->player->notification = true;
                    $this->player->save();
                    return trans('profile.notification.disabled',[],$this->player->lang);
                }
                elseif(Str::startsWith('off', $this->args[1]))
                {
                    $this->player->notification = false;
                    $this->player->save();
                    return trans('profile.notification.enabled',[],$this->player->lang);
                }
                else
                    return trans('profile.notification.missingParameter',[],$this->player->lang);
            }


            $totalPlayers = DB::table('players')->where('npc', 0)->count();
            $generalPosition = DB::table('players')->where([['npc', 0],['points_total', '>' , $this->player->points_total]])->count() + 1;
            $buildingPosition = DB::table('players')->where([['npc', 0],['points_building', '>' , $this->player->points_building]])->count() + 1;
            $researchPosition = DB::table('players')->where([['npc', 0],['points_research', '>' , $this->player->points_research]])->count() + 1;
            $militaryPosition = DB::table('players')->where([['npc', 0],['points_military', '>' , $this->player->points_military]])->count() + 1;

            if($this->player->notification)
                $notificationString = "On";
            else
                $notificationString = "Off";

            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => $this->player->user_name,
                "description" => "Lang: ".config('stargate.emotes.'.$this->player->lang)."\n"
                                ."Notification: ".$notificationString."\n"
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
