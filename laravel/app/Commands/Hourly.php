<?php

namespace App\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;

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
                    elseif($randomRes < 65)
                        $resType = 'gold';
                    elseif($randomRes < 80)
                        $resType = 'quartz';
                    else
                        $resType = 'naqahdah';
        
                    $varProd = 'production_'.$resType;

                    $multiplier = 1;
                    $this->player->hr_combo++;
                    if($comboReset)
                        $this->player->hr_combo = 1;
                    
                    $displayMultiplier = ($this->player->hr_combo-1) * 10;
                    $multiplier = 1+($displayMultiplier / 100);
                    
                    if($this->player->hr_combo > $this->player->hr_max_combo)
                        $this->player->hr_max_combo = $this->player->hr_combo;

                    $resValue = ($this->player->colonies[0]->$varProd / 60)* 30 * $multiplier;

                    $reward = config('stargate.emotes.'.strtolower($resType))." ".ucfirst($resType).': '.number_format($resValue).' (Combo: +'.$displayMultiplier.'%)';

                    $this->player->colonies[0]->$resType += $resValue;
                    $this->player->colonies[0]->save();

                    $this->player->last_hourly = Carbon::now();
                    $this->player->save();

                    return trans('hourly.hourlyReward', ['reward' => $reward], $this->player->lang);
                }
            }
            else
                return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }
}