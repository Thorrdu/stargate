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

            /*
            if($this->player->user_id == 125641223544373248 && count($this->args) >= 1)
            {
                $player = Player::where('user_id', $this->args[0])->first();
                $this->player = $player;
            }*/


            if(!empty($this->args))
            {
                if(Str::startsWith('notification', $this->args[0]))
                {
                    if(count($this->args) < 2)
                        return trans('profile.notification.missingParameter',[],$this->player->lang);
                    if(Str::startsWith('on', $this->args[1]))
                    {
                        $this->player->notification = true;
                        $this->player->save();
                        return trans('profile.notification.enabled',[],$this->player->lang);
                    }
                    elseif(Str::startsWith('off', $this->args[1]))
                    {
                        $this->player->notification = false;
                        $this->player->save();
                        return trans('profile.notification.disabled',[],$this->player->lang);
                    }
                    else
                        return trans('profile.notification.missingParameter',[],$this->player->lang);
                }
                if(Str::startsWith('hide', $this->args[0]))
                {
                    if(count($this->args) < 2)
                        return trans('profile.hide.missingParameter',[],$this->player->lang);
                    if(Str::startsWith('on', $this->args[1]))
                    {
                        $this->player->hide_coordinates = true;
                        $this->player->save();
                        return trans('profile.hide.enabled',[],$this->player->lang);
                    }
                    elseif(Str::startsWith('off', $this->args[1]))
                    {
                        $this->player->hide_coordinates = false;
                        $this->player->save();
                        return trans('profile.hide.disabled',[],$this->player->lang);
                    }
                    else
                        return trans('profile.hide.missingParameter',[],$this->player->lang);
                }
                if(Str::startsWith('vacation', $this->args[0]))
                {
                    if(is_null($this->player->vacation))
                    {
                        $now = Carbon::now();
                        if(!is_null($this->player->next_vacation) && $this->player->next_vacation > $now)
                        {
                            $nextVacation = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->next_vacation);
                            $nextVacationString = $now->diffForHumans($nextVacation,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                            return trans('profile.nextVacation', ['time' => $nextVacationString], $this->player->lang);
                        }

                        $fightLast2Hours = GateFight::Where([['active', true],['player_id_source',$this->player->id],['created_at', '>=', Carbon::now()->sub('12h')]])->orderBy('created_at','DESC')->get();
                        if($fightLast2Hours->count() > 0)
                        {
                            $nextVacation = Carbon::createFromFormat("Y-m-d H:i:s",$fightLast2Hours)->add('12h');
                            $nextVacationString = $now->diffForHumans($nextVacation,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                            return trans('profile.youFightedRecently', ['time' => $nextVacationString], $this->player->lang);
                        }

                        if($this->player->activeFleets->count() > 0)
                        {
                            return trans('profile.activeFleets', [], $this->player->lang);
                        }

                        if($this->player->active_technology_id != null)
                            return trans('profile.busyPlayer', [], $this->player->lang);
                        foreach($this->player->colonies as $colony)
                        {
                            if($colony->active_building_id != null || $colony->craftQueues->count() > 0 || $colony->defenceQueues->count() > 0 || $colony->shipQueues->count() > 0)
                                return trans('profile.busyPlayer', [], $this->player->lang);
                        }

                        if($this->player->activeFleets->count() > 0)
                        {
                            return trans('profile.busyPlayer', [], $this->player->lang);
                        }

                        //proposition vacances
                        $upgradeMsg = trans('profile.vacationConfirm', [], $this->player->lang);

                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage($upgradeMsg)->then(function ($messageSent){

                            $this->closed = false;
                            $this->paginatorMessage = $messageSent;
                            $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                });
                            });

                            $filter = function($messageReaction){
                                return $messageReaction->user_id == $this->player->user_id;
                            };
                            $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector){
                                $messageReaction = $collector->first();
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                    {
                                        $this->player->vacation = Carbon::now();
                                        $this->player->save();
                                        $this->paginatorMessage->content = trans('profile.vacationActivated', [], $this->player->lang);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                        return;
                                    }
                                    elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $this->paginatorMessage->content = trans('generic.cancelled', [], $this->player->lang);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                    }
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                }
                                catch(\Exception $e)
                                {
                                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                }
                            });
                        });
                    }
                    else{
                        $now = Carbon::now();
                        $vacationUntil = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->vacation)->add('72h');

                        if($vacationUntil > $now)
                        {
                            $vacationUntilString = $now->diffForHumans($vacationUntil,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                            return trans('profile.vacationUntil', ['time' => $vacationUntilString], $this->player->lang);
                        }

                        //proposition désactivation vacances
                        $upgradeMsg = trans('profile.vacationOverConfirm', [], $this->player->lang);

                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage($upgradeMsg)->then(function ($messageSent){

                            $this->closed = false;
                            $this->paginatorMessage = $messageSent;
                            $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                });
                            });

                            $filter = function($messageReaction){
                                return $messageReaction->user_id == $this->player->user_id;
                            };
                            $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector){
                                $messageReaction = $collector->first();
                                try{
                                    if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                    {
                                        $this->player->next_vacation = Carbon::now()->add('72h');
                                        $this->player->vacation = null;
                                        $this->player->save();
                                        $now = Carbon::now();
                                        foreach($this->player->colonies as $colony)
                                        {
                                            $colony->last_claim = $now;
                                            $colony->save();
                                        }
                                        $this->paginatorMessage->content = trans('profile.vacationOver', [], $this->player->lang);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                        return;
                                    }
                                    elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                    {
                                        $this->paginatorMessage->content = trans('generic.cancelled', [], $this->player->lang);
                                        $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                        $this->closed = true;
                                    }
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                }
                                catch(\Exception $e)
                                {
                                    echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                }
                            });
                        });

                    }
                    return;
                }
            }


            $totalPlayers = DB::table('players')->where('npc', 0)->count();
            $generalPosition = DB::table('players')->where([['id', '!=', 1],['npc', 0],['points_total', '>' , $this->player->points_total]])->count() + 1;
            $buildingPosition = DB::table('players')->where([['id', '!=', 1],['npc', 0],['points_building', '>' , $this->player->points_building]])->count() + 1;
            $researchPosition = DB::table('players')->where([['id', '!=', 1],['npc', 0],['points_research', '>' , $this->player->points_research]])->count() + 1;
            $craftPosition = DB::table('players')->where([['id', '!=', 1],['npc', 0],['points_craft', '>' , $this->player->points_craft]])->count() + 1;
            $militaryPosition = DB::table('players')->where([['id', '!=', 1],['npc', 0],['points_military', '>' , $this->player->points_military]])->count() + 1;
            $defencePosition = DB::table('players')->where([['id', '!=', 1],['npc', 0],['points_defence', '>' , $this->player->points_defence]])->count() + 1;

            if($this->player->notification)
                $notificationString = "On";
            else
                $notificationString = "Off";

            $embed = [
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
                                  .config('stargate.emotes.military')." ".trans('generic.military',[],$this->player->lang).": Points ".number_format($this->player->points_military)." (Position: ".number_format($militaryPosition)."/{$totalPlayers})\n"
                                  .config('stargate.emotes.defence')." ".trans('generic.defence',[],$this->player->lang).": Points ".number_format($this->player->points_defence)." (Position: ".number_format($defencePosition)."/{$totalPlayers})\n",
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
            }
            return ;
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
