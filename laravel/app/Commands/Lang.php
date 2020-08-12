<?php

namespace App\Commands;

class Lang extends CommandHandler implements CommandInterface
{
    public $maxTime;
    public $paginatorMessage;
    public $listner;

    public function execute()
    {
        echo PHP_EOL.'Execute Lang';

        if(is_null($this->player))
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

        if($this->player->ban)
            return trans('generic.banned',[],$this->player->lang);
                
        if($this->player->captcha)
            return trans('generic.captchaMessage',[],$this->player->lang);

        if(empty($this->args))
            return trans('lang.choice', [], $this->player->lang);

        if(in_array($this->args[0],array('en','fr')))
        {
            $this->player->lang = $this->args[0];
            $this->player->save();
            return trans('lang.updated', [], $this->player->lang);
        }
        else
            return trans('lang.choice', [], $this->player->lang);
    }
}
