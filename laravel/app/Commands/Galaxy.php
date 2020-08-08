<?php

namespace App\Commands;

use Illuminate\Support\Facades\DB;
use App\Coordinate;

class Galaxy extends CommandHandler implements CommandInterface
{
    public $galaxy;
    public $system;
    public $maxGalaxyPage;
    public $maxSystemPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;

    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Galaxy';

            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            try{
                
                $this->galaxy = $this->player->colonies[0]->coordinates->galaxy;
                $this->system = $this->player->colonies[0]->coordinates->system;

                $this->maxGalaxyPage = config('stargate.galaxy.maxGalaxies');
                $this->maxSystemPage = config('stargate.galaxy.maxSystems');
                $this->maxTime = time()+180;
                $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                    $this->paginatorMessage = $messageSent;
                    
                    $this->paginatorMessage->react('⏮️')->then(function(){
                        $this->paginatorMessage->react('⏭️')->then(function(){
                            $this->paginatorMessage->react('⏪')->then(function(){ 
                                $this->paginatorMessage->react('◀️')->then(function(){ 
                                    $this->paginatorMessage->react('▶️')->then(function(){ 
                                        $this->paginatorMessage->react('⏩');
                                    });
                                });
                            });
                        });
                    });
                    $this->listner = function ($messageReaction) {
                        if($this->maxTime < time())
                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);

                        if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                        {
                            if($messageReaction->emoji->name == '⏭️' && $this->maxGalaxyPage > $this->galaxy)
                            {
                                $this->galaxy++;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '⏮️' && $this->galaxy > 1)
                            {
                                $this->galaxy--;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            if($messageReaction->emoji->name == '⏪')
                            {
                                $this->system = 1;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '◀️' && $this->system > 1)
                            {
                                $this->system--;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '▶️' && $this->maxSystemPage > $this->system)
                            {
                                $this->system++;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '⏩')
                            {
                                $this->system = $this->maxSystemPage;
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
        try{
            $coordinates = Coordinate::where([['galaxy', $this->galaxy],['system', $this->system]])->get();
            
            $coordinateList = "";
            foreach($coordinates as $coordinate)
            {
                if(!is_null($coordinate->colony))
                    $coordinateList .= $coordinate->planet." - ".$coordinate->colony->name." (".$coordinate->colony->player->user_name.")"."\n";
                else
                    $coordinateList .= $coordinate->planet."\n";
            }

            $embed = [
                'author' => [
                    'name' => "Stargate",
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/267e7aa294e04be5fba9a70c4e89e292.png'
                ],
                "title" => "Galaxy ".$this->galaxy." - System ".$this->system,
                "description" => "Liste des planètes du système:\n\n".$coordinateList,
                'fields' => [],
                'footer' => array(
                    'text'  => 'Stargate',
                ),
            ];
            return $embed;
            }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }
}
