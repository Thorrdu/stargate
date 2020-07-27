<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;


class Paginator extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxTime;
    public $paginatorMessage;
    public $listner;

    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return 'Vous êtes banni...';

            try{
                $this->page = 1;
                $this->maxTime = time()+180;
                $this->message->channel->sendMessage($this->getTop())->then(function ($messageSent){
                    $this->paginatorMessage = $messageSent;
                    $this->paginatorMessage->react('◀️')->then(function(){ 
                        $this->paginatorMessage->react('▶️');
                    });

                    $this->listner = function ($messageReaction) {
                        if($this->maxTime < time())
                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                        if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                        {
                            if($messageReaction->emoji->name == '◀️')
                            {
                                $this->page--;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,$this->getTop());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '▶️')
                            {
                                $this->page++;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,$this->getTop());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                        }
                    };
                    $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                });
            }
            catch(\Exception $e)
            {
                echo $e->getMessage();
                return $e->getMessage();
            }
        }
        else
            return "Pour commencer votre aventure, utilisez `!start`";
        return false;
    }

    public function getTop()
    {
        return 'Page Test '.$this->page;
    }
}
