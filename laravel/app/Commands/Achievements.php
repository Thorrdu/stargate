<?php

namespace App\Commands;

use App\GateFight;
use App\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Discord\Parts\Embed\Embed;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Channel\Message;

class Achievements extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Achievements';
            if($this->player->ban)
                return trans('generic.banned', [], $this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode', [], $this->player->lang);

            if(!empty($this->args))
            {
                if(Str::startsWith('*******', $this->args[0]))
                {

                }
                else
                {
                    //Quest detail ?
                }
            }
            else
            {
                //Quest list
                /*$embed = [
                    'author' => [
                        'name' => $this->player->user_name,
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    "title" => $this->player->user_name,
                    "description" => "Lang: ".config('stargate.emotes.'.$this->player->lang)."\n"
                                    ."Notification: ".$notificationString."\n"
                                    ."Combo Hourly Max: ".$this->player->hr_max_combo."\n"
                                    ."Votes: ".$this->player->votes."\n",
                    'fields' => [
                        [
                            'name' => 'Points',
                            'value' => trans('generic.general',[],$this->player->lang).": ".number_format($this->player->points_total)." Points (Position: ".number_format($generalPosition)."/{$totalPlayers})\n"
                                      .config('stargate.emotes.productionBuilding')." ".trans('generic.building',[],$this->player->lang).": Points ".number_format($this->player->points_building)." (".number_format($buildingPosition)."/{$totalPlayers})\n"
                                      .config('stargate.emotes.research')." ".trans('generic.research',[],$this->player->lang).": Points ".number_format($this->player->points_research)." (Position: ".number_format($researchPosition)."/{$totalPlayers})\n"
                                      .config('stargate.emotes.craft')." ".trans('generic.unit',[],$this->player->lang).": Points ".number_format($this->player->points_craft)." (Position: ".number_format($craftPosition)."/{$totalPlayers})\n"
                                      .config('stargate.emotes.military')." ".trans('generic.military',[],$this->player->lang).": Points ".number_format($this->player->points_military)." (Position: ".number_format($militaryPosition)."/{$totalPlayers})\n",
                                      //.config('stargate.emotes.defence')." ".trans('generic.defence',[],$this->player->lang).": Points ".number_format($this->player->points_defence)." (Position: ".number_format($defencePosition)."/{$totalPlayers})\n",
                            'inline' => true
                        ]
                    ],
                    'footer' => array(
                        'text'  => 'Stargate',
                    ),
                ];
                try{
                    $newEmbed = $this->discord->factory(Embed::class,$embed);
                    $this->message->channel->sendMessage('', false, $newEmbed);
                }catch(\Exception $e)
                {
                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                }*/
            }

            return ;
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
