<?php

namespace App\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;
use App\Reminder;

class Hourly extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        try{
            if(!is_null($this->player))
            {
                echo PHP_EOL.'Execute Hourly';
                if($this->player->ban)
                    return trans('generic.banned',[],$this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode',[],$this->player->lang);

                $hourlyOk = $comboReset = false;
                if(!is_null($this->player->last_hourly))
                {
                    $now = Carbon::now();
                    $lastHourly = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->last_hourly);

                    if($lastHourly->diffInHours($now) >= 2)
                    {
                        $hourlyOk = true;
                        $comboReset = true;
                    }
                    elseif($lastHourly->diffInHours($now) >= 1)
                    {
                        $hourlyOk = true;
                    }
                    else
                    {
                        $lastHourly->add('1h');
                        $nextHourlyTime = $now->diffForHumans($lastHourly,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        return trans('hourly.hourlyWaiting', ['time' => $nextHourlyTime], $this->player->lang);
                    }
                }
                else
                    $hourlyOk = true;

                if($hourlyOk)
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

                    $multiplier = 1;
                    $this->player->hr_combo++;
                    if($comboReset)
                        $this->player->hr_combo = 1;

                    if($this->player->hr_combo > config('stargate.maxHourly'))
                    {
                        $displayMultiplier = (config('stargate.maxHourly') * 10) + $this->player->hr_combo;
                        $multiplier = 1+($displayMultiplier / 100);
                    }
                    else
                    {
                        $displayMultiplier = ($this->player->hr_combo-1) * 10;
                        $multiplier = 1+($displayMultiplier / 100);
                    }

                    if($this->player->hr_combo > $this->player->hr_max_combo)
                        $this->player->hr_max_combo = $this->player->hr_combo;

                    $totalProd = 0;
                    foreach($this->player->colonies as $colony)
                        $totalProd += $colony->$varProd;
                    $totalProd /= $this->player->colonies->count();

                    $resValue = ($totalProd / 60)* rand(15,30) * $multiplier;

                    $reward = config('stargate.emotes.'.strtolower($resType))." ".ucfirst($resType).': '.number_format($resValue).' (Combo: '.$this->player->hr_combo.' (+'.$displayMultiplier.'%))';

                    $newResValue = $this->player->activeColony->$resType + $resValue;
                    if(($this->player->activeColony->{'storage_'.$resType}*1.25) <= $newResValue)
                        $newResValue = $this->player->activeColony->{'storage_'.$resType}*1.25;

                    $this->player->activeColony->$resType = $newResValue;
                    $this->player->activeColony->save();

                    $this->player->last_hourly = Carbon::now();
                    /**CAPTCHA SECURITY*/
                    if($this->player->hr_combo > 4 && $this->player->hr_combo % 2 == 0)
                    {
                        $this->player->captcha = true;
                        $this->player->captcha_key = Str::random(10);

                        $userExist = $this->discord->factory(\Discord\Parts\User\User::class, [
                            'id' => $this->player->user_id,
                        ]);
                        if(!is_null($userExist))
                            $userExist->sendMessage(trans('generic.captchaLink', ['link' => 'https://web.thorr.ovh/captcha/'.$this->player->captcha_key], $this->player->lang));
                    }
                    $this->player->save();

                    if($this->player->notification)
                    {
                        $reminder = new Reminder;
                        $reminder->reminder_date = Carbon::now()->add('1h');
                        $reminder->title = trans('reminder.titles.notification', [], $this->player->lang);
                        $reminder->reminder = trans("hourly.hourlyAvailable", [], $this->player->lang);
                        $reminder->player_id = $this->player->id;
                        $reminder->save();
                    }

                    return trans('hourly.hourlyReward', ['reward' => $reward], $this->player->lang);
                }
            }
            else
                return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }
}
