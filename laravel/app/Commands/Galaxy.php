<?php

namespace App\Commands;

use Illuminate\Support\Facades\DB;
use App\Coordinate;
use App\Technology;
use Illuminate\Support\Str;
use Discord\Parts\Embed\Embed;
use \Discord\Parts\Channel\Message as Message;

class Galaxy extends CommandHandler implements CommandInterface
{
    public $galaxy;
    public $system;
    public $maxGalaxyPage;
    public $maxSystemPage;
    public $systemRestrictionMin;
    public $systemRestrictionMax;
    public $systemRestriction;
    public $galaxyRestriction;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $closed;


    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Galaxy';

            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);
                    
            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            try{
                $this->galaxy = $this->player->activeColony->coordinates->galaxy;
                $this->system = $this->player->activeColony->coordinates->system;

                $this->closed = false;
                $this->galaxyRestriction = false;
                $this->systemRestriction = false;
                $communication = Technology::find(1);
                $comLvl = $this->player->hasTechnology($communication);
                if($comLvl)
                {
                    if($comLvl < 8)
                        $this->galaxyRestriction = true;
                    $maxMovement = pow(2,$comLvl);
                    $this->systemRestrictionMin = $this->system-$maxMovement;
                    $this->systemRestrictionMax = $this->system+$maxMovement;
                }
                else
                {
                    $this->systemRestriction = true;
                    $this->galaxyRestriction = true;
                }

                $this->maxGalaxyPage = config('stargate.galaxy.maxGalaxies');
                $this->maxSystemPage = config('stargate.galaxy.maxSystems');
                $this->maxTime = time()+180;
                $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                    $this->paginatorMessage = $messageSent;
                    
                    if(!$this->systemRestriction)
                    {
                        if(!$this->galaxyRestriction)
                        {
                            $this->paginatorMessage->react('⏮️')->then(function(){
                                $this->paginatorMessage->react('⏭️')->then(function(){
                                    $this->paginatorMessage->react('⏪')->then(function(){ 
                                        $this->paginatorMessage->react('◀️')->then(function(){ 
                                            $this->paginatorMessage->react('▶️')->then(function(){ 
                                                $this->paginatorMessage->react('⏩')->then(function(){
                                                    $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                                });
                                            });
                                        });
                                    });
                                });
                            });
                        }
                        else
                        {
                            $this->paginatorMessage->react('⏪')->then(function(){ 
                                $this->paginatorMessage->react('◀️')->then(function(){ 
                                    $this->paginatorMessage->react('▶️')->then(function(){ 
                                        $this->paginatorMessage->react('⏩')->then(function(){
                                            $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                        });
                                    });
                                });
                            });

                        }
                    }



                    $filter = function($messageReaction){
                        if($messageReaction->user_id != $this->player->user_id || $this->closed == true)
                            return false;
                        
                        if($messageReaction->user_id == $this->player->user_id)
                        {
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->player->lang)]);
                                    $messageReaction->message->addEmbed($newEmbed);
                                    $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                                    $this->closed = true;
                                }
                                elseif(!$this->galaxyRestriction && $messageReaction->emoji->name == '⏭️' && $this->maxGalaxyPage > $this->galaxy)
                                {
                                    $this->galaxy++;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif(!$this->galaxyRestriction && $messageReaction->emoji->name == '⏮️' && $this->galaxy > 1)
                                {
                                    $this->galaxy--;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '⏪')
                                {
                                    $this->system = 1;
                                    if($this->systemRestrictionMin > 1)
                                        $this->system = $this->systemRestrictionMin;
    
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '◀️'  && $this->system > 1 && $this->system > $this->systemRestrictionMin)
                                {
                                    $this->system--;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '▶️' && $this->maxSystemPage > $this->system && $this->system < $this->systemRestrictionMax)
                                {
                                    $this->system++;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '⏩')
                                {
                                    $this->system = $this->maxSystemPage;
                                    if($this->systemRestrictionMax < $this->system)
                                        $this->system = $this->systemRestrictionMax;

                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                $messageReaction->message->deleteReaction(Message::REACT_DELETE_ID, urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            catch(\Exception $e)
                            {
                                echo $e->getMessage();
                            }
                            return true;
                        }
                        else
                            return false;
                    };
                    if(!$this->systemRestriction)
                        $this->paginatorMessage->createReactionCollector($filter);
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
                {                    
                    $colonyPlayer = $coordinate->colony->player;
                    if($colonyPlayer->npc)
                        $coordinateList .= $coordinate->planet." - ".$coordinate->colony->name." [NPC] ".$colonyPlayer->user_name."\n";
                    else
                        $coordinateList .= $coordinate->planet." - ".$this->player->isWeakOrStrong($colonyPlayer)." ".$coordinate->colony->name." ".$colonyPlayer->user_name."\n";
                }
                else
                    $coordinateList .= $coordinate->planet."\n";
            }

            $embed = [
                'author' => [
                    'name' => "Galaxy",
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => "Galaxy ".$this->galaxy." - System ".$this->system,
                "description" => trans('galaxy.systemList', [], $this->player->lang).$coordinateList,
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
