<?php

namespace App\Commands;

use App\Reminder as ReminderModel;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class Reminder extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            echo PHP_EOL.'Execute Reminder';

            if(count($this->args) < 2)
                return trans('reminder.wrongParameter', [], $this->player->lang);
            
            try{
                $reason = substr(implode(' ',$this->args),strlen($this->args[0]));
                $reminder = new ReminderModel;
                $reminder->reminder_date = Carbon::now()->add($this->args[0]);
                $reminder->reminder = substr(implode(' ',$this->args),strlen($this->args[0]));
                $reminder->player_id = $this->player->id;
                $reminder->save();
                $now = Carbon::now();
                return trans('reminder.confirm', ['time' => $now->diffForHumans($reminder->reminder_date), 'reason' => $reason], $this->player->lang);
            }
            catch(\Exception $e)
            {
                return trans('reminder.wrongParameter', [], $this->player->lang);
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

    }
}
