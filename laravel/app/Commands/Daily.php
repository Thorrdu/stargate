<?php

namespace App\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use App\Reminder;

class Daily extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        try{
            if(!is_null($this->player))
            {
                echo PHP_EOL.'Execute Daily';
                if($this->player->ban)
                    return trans('generic.banned',[],$this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $dailyOK = false;
                if(!is_null($this->player->last_daily))
                {
                    $now = Carbon::now();
                    $lastDaily = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->last_daily);

                    if($lastDaily->diffInHours($now) >= 24)
                    {
                        $dailyOK = true;
                    }
                    else
                    {
                        $lastDaily->add('24h');
                        $nextDailyTime = $now->diffForHumans($lastDaily,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('daily.dailyWaiting', ['time' => $nextDailyTime], $this->player->lang);
                    }
                }
                else
                    $dailyOK = true;


                if($dailyOK)
                {
                    $randomRes = rand(1,100);
                    if($randomRes < 45)
                        $resType = 'iron';
                    elseif($randomRes < 70)
                        $resType = 'gold';
                    elseif($randomRes < 85)
                        $resType = 'quartz';
                    else
                        $resType = 'naqahdah';

                    $varProd = 'production_'.$resType;

                    $totalProd = 0;
                    foreach($this->player->colonies as $colony)
                        $totalProd += $colony->$varProd;
                    $totalProd /= $this->player->colonies->count();
                    $resValue = $totalProd * rand(2,5);
                    $reward = config('stargate.emotes.'.strtolower($resType))." ".ucfirst($resType).': '.number_format($resValue);

                    $newResValue = $this->player->activeColony->$resType + $resValue;
                    if(($this->player->activeColony->{'storage_'.$resType}*1.25) <= $newResValue)
                        $newResValue = $this->player->activeColony->{'storage_'.$resType}*1.25;

                    $this->player->activeColony->$resType = $newResValue;
                    $this->player->activeColony->save();

                    $this->player->last_daily = Carbon::now();
                    $this->player->dailies++;
                    $this->player->save();

                    if($this->player->notification)
                    {
                        $reminder = new Reminder;
                        $reminder->reminder_date = Carbon::now()->add('24h');
                        $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                        $reminder->reminder = trans("daily.dailyAvailable", [], $this->player->lang);
                        $reminder->player_id = $this->player->id;
                        $reminder->save();
                    }


                    return trans('daily.dailyReward', ['reward' => $reward], $this->player->lang);
                }
            }
            else
                return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        }
        catch(\Exception $e)
        {

            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }
}
