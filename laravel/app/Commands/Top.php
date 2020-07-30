<?php

namespace App\Commands;

use App\Player;
use Illuminate\Support\Str;

class Top extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $perPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $playerList;
    public $topType;

    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Top';

            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            try{
                if(empty($this->args))
                    return trans('top.choice', [], $this->player->lang);

                if(Str::startsWith('general', $this->args[0])){
                    $this->topType = 'general';
                    $this->playerList = Player::all()->sortByDesc('points_total'); 
                }     
                elseif(Str::startsWith('building', $this->args[0])){
                    $this->topType = 'building';
                    $this->playerList = Player::all()->sortByDesc('points_building');
                }      
                elseif(Str::startsWith('research', $this->args[0])){
                    $this->topType = 'research';
                    $this->playerList = Player::all()->sortByDesc('points_research');   
                }  
                elseif(Str::startsWith('military', $this->args[0])){
                    $this->topType = 'military';
                    $this->playerList = Player::all()->sortByDesc('points_military');   
                }   
                else
                    return trans('top.choice', [], $this->player->lang);
                  
                $this->page = 1;
                $this->perPage = 10;
                $this->maxPage = ceil($this->playerList->count()/$this->perPage);
                $this->maxTime = time()+180;
                $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                    $this->paginatorMessage = $messageSent;
                    $this->paginatorMessage->react('⏪')->then(function(){ 
                        $this->paginatorMessage->react('◀️')->then(function(){ 
                            $this->paginatorMessage->react('▶️')->then(function(){ 
                                $this->paginatorMessage->react('⏩');
                            });
                        });
                    });

                    $this->listner = function ($messageReaction) {
                        if($this->maxTime < time())
                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                        if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                        {
                            if($messageReaction->emoji->name == '⏪')
                            {
                                $this->page = 1;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                            {
                                $this->page--;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                            {
                                $this->page++;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '⏩')
                            {
                                $this->page = $this->maxPage;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
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
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }

    public function getPage()
    {
        $displayList = $this->playerList->skip($this->perPage*($this->page -1))->take($this->perPage);
        if($this->topType == 'general')
            $varName = 'points_total';
        else
            $varName = 'points_'.$this->topType;
        
        $counter = (($this->page-1)*$this->perPage)+1;

        $playerList = "";
        foreach($displayList as $player)
        {
            $playerList .= $counter.". ".$player->user_name.' - '.number_format($player->$varName)." Points\n";
            $counter++;
        }
        if(empty($playerList))
            $playerList = "/";

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
            ],
            "title" => 'Top '.trans('generic.'.$this->topType, [], $this->player->lang),
            "description" => $playerList,
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - Page '.$this->page.' / '.$this->maxPage,
            ),
        ];
        return $embed;
    }
}
