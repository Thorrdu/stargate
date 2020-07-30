<?php

namespace App\Commands;

use App\Player;

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

        if(empty($this->args))
            return trans('lang.choice', [], $this->player->lang);


        try{
            $this->maxTime = time()+180;
            $embed = [
                'author' => [
                    'name' => "Stargate",
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => $this->message->author->user_name,
                "description" => trans('lang.choice',[],'en'),
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                )
            ];

            $this->message->channel->sendMessage('',false, $embed)->then(function ($messageSent){
                $this->paginatorMessage = $messageSent;
                $this->paginatorMessage->react('🇬🇧')->then(function(){ 
                    $this->paginatorMessage->react('🇫🇷');
                });

                $this->listner = function ($messageReaction) {
                    if($this->maxTime < time())
                        $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                    if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->newPlayerId)
                    {
                        if($messageReaction->emoji->name == '🇫🇷')
                            $this->lang('fr');
                        elseif($messageReaction->emoji->name == '🇬🇧')
                            $this->lang('en');
                    }
                };
                $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);

            });
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function lang($lang)
    {
        $this->player->lang = $lang;
        $this->player->save();
        try{
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => "Welcome to Stargate",
                "description" => trans('lang.updated', [], $this->player->lang),
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                )
            ];

            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, '',$embed);
            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
