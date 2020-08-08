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
                $reminder = new ReminderModel;
                $reminder->reminder_date = Carbon::now()->add($this->args[0]);
                $reminder->reminder = substr($this->message,strlen($this->args[0]));
                $reminder->player_id = $this->player->id;
                $reminder->save();
            }
            catch(\Exception $e)
            {
                return $e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

    }
}
