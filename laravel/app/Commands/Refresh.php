<?php

namespace App\Commands;

use App\Player;

class Refresh extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned', [], $this->player->lang);
                    
            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

                
                    
                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);
            $this->player->activeColony->calcProd();
            $this->player->activeColony->save();
            return "Prod recalculée";
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
