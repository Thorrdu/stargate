<?php

namespace App\Commands;

use App\Reminder as ReminderModel;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class Reminder extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            echo PHP_EOL.'Execute Reminder';

            if(!empty($this->args) && Str::startsWith('list', $this->args[0]))
            {
                $reminderString = "";
                if(!empty($this->player->reminders))
                {
                    $now = Carbon::now();
                    foreach($this->player->reminders as $reminder)
                    {
                        $reminderTimeString = $now->diffForHumans($reminder->reminder_date,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $reminderString .= "`".$reminder->id."` - ".$reminder->reminder_date." (".$reminderTimeString.") - `".str_replace("**Reminder:** ","",$reminder->reminder)."`\n";
                    }
                }
                return "__".trans('reminder.listTitle', [], $this->player->lang)."__:\n\n".$reminderString;
            }

            if(count($this->args) < 2)
                return trans('reminder.wrongParameter', [], $this->player->lang);
            
            if(Str::startsWith('remove', $this->args[0]))
            {
                try{
                    $reminderString = "";
                    if(is_numeric($this->args[1]))
                    {
                        $reminder = ReminderModel::find($this->args[0]);
                        if(!is_null($reminder) && $reminder->player->id == $this->player->id)
                        {
                            $reminder->delete();
                            return trans('reminder.removed', [], $this->player->lang);
                        }
                        else
                        {
                            return $reminder->player_id;
                        }
                    }
                    else
                    {
                        return $this->args[1].' Not integer ???';
                    }
                    return trans("reminder.unknown", [], $this->player->lang);
                }
                catch(\Exception $e)
                {
                    return $e->getMessage();
                    return trans('reminder.wrongParameter', [], $this->player->lang);
                }
            }

            try{
                $reason = trim(substr(implode(' ',$this->args),strlen($this->args[0])));
                $reminder = new ReminderModel;
                $reminder->reminder_date = Carbon::now()->add($this->args[0]);
                $reminder->reminder = "**Reminder:** ".$reason;
                $reminder->player_id = $this->player->id;
                $reminder->save();
                $now = Carbon::now();
                $reminderTimeString = $now->diffForHumans($reminder->reminder_date,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
                return trans('reminder.confirm', ['time' => $reminderTimeString, 'reason' => $reason], $this->player->lang);
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
