<?php

namespace App\Commands;

use App\Player;

class Start extends CommandHandler implements CommandInterface
{
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $buildingList;

    public function execute()
    {
        echo PHP_EOL.'Execute Start';
        if(is_null($this->player))
        {
            $this->maxTime = time()+180;
            $this->message->channel->sendMessage('', false, trans('start.langChoice',[],'en')."\n\n".trans('start.langChoice',[],'fr'))->then(function ($messageSent){
                $this->paginatorMessage = $messageSent;
                $this->paginatorMessage->react('🇬🇧')->then(function(){ 
                    $this->paginatorMessage->react('🇫🇷');
                });

                $this->listner = function ($messageReaction) {
                    if($this->maxTime < time())
                        $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                    if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                    {
                        if($messageReaction->emoji->name == '🇫🇷')
                            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->start('fr'));
                        elseif($messageReaction->emoji->name == '🇬🇧' )
                            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->start('en'));
                    }
                };
                $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
            });
        }
        elseif($this->player->ban)
            return trans('generic.banned',[],$this->player->lang);
        else
            return trans('start.alreadyExists',[],$this->player->lang);
    }

    public function start($lang)
    {
        $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
        $newPlayer = new Player;
        $newPlayer->user_id = $this->message->author->id;
        $newPlayer->user_name = $this->message->author->user_name;
        $newPlayer->ban = false;
        $newPlayer->lang = $lang;
        $newPlayer->votes = 0;
        $newPlayer->save();   
        $newPlayer->addColony();
        return trans('start.startMessage',[],'fr');

    }
}
