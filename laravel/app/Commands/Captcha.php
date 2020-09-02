<?php

namespace App\Commands;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Captcha extends CommandHandler implements CommandInterface
{
    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Captcha';
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
            {
                $userExist = $this->discord->users->get('id',$this->player->user_id);
                if(!is_null($userExist))
                    $userExist->sendMessage(trans('generic.captchaLink', ['link' => 'https://web.thorr.ovh/captcha/'.$this->player->captcha_key], $this->player->lang));
                return trans('generic.newCaptchaMessage', [], $this->player->lang);
            }
            else
                return trans('generic.noCaptcha',[],$this->player->lang);
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
    }
}
