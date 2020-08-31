<?php

namespace App\Commands;

use App\Player;
use Discord\Parts\Embed\Embed;


class Start extends CommandHandler implements CommandInterface
{
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $buildingList;
    public $newPlayerId;
    public $userName;
    public $messageReaction;

    public function execute()
    {
        echo PHP_EOL.'Execute Start ';
        if(is_null($this->player))
        {
            try{

                if(!is_null($this->message->author->user->username ))
                    $this->userName = $this->message->author->user->username ;
                else
                    $this->userName = $this->message->author->user->username ;

                $this->newPlayerId = $this->message->author->id;
                $this->maxTime = time()+180;
                $embed = [
                    'author' => [
                        'name' => "Stargate",
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    "title" => $this->userName,
                    "description" => trans('start.langChoice',[],'en')."\n\n".trans('start.langChoice',[],'fr'),
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

                        if($messageReaction->message->id == $this->paginatorMessage->id && $messageReaction->user_id == $this->message->author->id)
                        {
                            $this->messageReaction = $messageReaction;

                            if($messageReaction->emoji->name == '🇫🇷')
                                $this->start('fr');
                            elseif($messageReaction->emoji->name == '🇬🇧')
                                $this->start('en');
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
        elseif($this->player->ban)
            return trans('generic.banned',[],$this->player->lang);
        elseif($this->player->captcha)
            return trans('generic.captchaMessage',[],$this->player->lang);
        else
            return trans('start.accountExists',[],$this->player->lang);
    }

    public function start($lang)
    {
        try{
            $newPlayer = new Player;
            $newPlayer->user_id = $this->newPlayerId;
            $newPlayer->user_name = $this->userName;
            $newPlayer->ban = false;
            $newPlayer->lang = $lang;
            $newPlayer->votes = 0;
            $newPlayer->save();   
            $newPlayer->addColony();

            $embed = [
                'author' => [
                    'name' => $newPlayer->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => "Welcome to Stargate",
                "description" => trans('start.startMessage',[],$newPlayer->lang),
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                )
            ];
            $newEmbed = $this->discord->factory(Embed::class,$embed);
            $this->paginatorMessage->addEmbed($newEmbed);
            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }
    }
}
