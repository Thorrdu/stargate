<?php

namespace App\Commands;

class Reminder extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            return 'Execute Reminder';

            echo PHP_EOL.'Execute Reminder';
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

    }
}
