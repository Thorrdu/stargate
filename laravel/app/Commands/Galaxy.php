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

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

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
                    $this->systemRestrictionMin = $this->system;
                    $this->systemRestrictionMax = $this->system;
                }

                $this->maxGalaxyPage = config('stargate.galaxy.maxGalaxies');
                $this->maxSystemPage = config('stargate.galaxy.maxSystems');

                if(!empty($this->args) && preg_match('/(([0-9]{1,}:[0-9]{1,})|([0-9]{1,};[0-9]{1,}))/', $this->args[0], $coordinatesMatch))
                {
                    if(strstr($coordinatesMatch[0],';'))
                        $coordinates = explode(';',$coordinatesMatch[0]);
                    else
                        $coordinates = explode(':',$coordinatesMatch[0]);

                    $wantedGalaxy = $coordinates[0];
                    $wantedSystem = $coordinates[1];

                    if($this->systemRestriction || ($this->galaxyRestriction && $wantedGalaxy != $this->galaxy)
                        || $wantedGalaxy > $this->maxGalaxyPage || $wantedGalaxy < 1
                        || $wantedSystem > $this->maxSystemPage || $wantedSystem > $this->systemRestrictionMax || $wantedSystem < 1 || $wantedSystem < $this->systemRestrictionMin
                    )
                        return trans('stargate.unReacheableCoordinates', [], $this->player->lang);
                    else
                    {
                        $this->galaxy = $wantedGalaxy;
                        $this->system = $wantedSystem;
                    }
                }


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
                                    return;
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
                                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                            }
                            return true;
                        }
                        else
                            return false;
                    };
                    if(!$this->systemRestriction)
                        $this->paginatorMessage->createReactionCollector($filter,['time' => 600000]);
                });

            }
            catch(\Exception $e)
            {
                echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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

            $spy = Technology::where('slug', 'spy')->first();
            $counterSpy = Technology::where('slug', 'counterspy')->first();

            $spyLvl = $this->player->hasTechnology($spy);
            if(!$spyLvl)
                $spyLvl = 0;

            $coordinateList = "";
            foreach($coordinates as $coordinate)
            {
                if(!is_null($coordinate->colony))
                {
                    $colonyPlayer = $coordinate->colony->player;
                    $colonyPlayerName = '';

                    if(!($colonyPlayer->alliance_id != null && $this->player->alliance_id != null && $this->player->alliance_id == $colonyPlayer->alliance_id))
                    {
                        $counterSpyLvl = $colonyPlayer->hasTechnology($counterSpy);
                        if(!$counterSpyLvl)
                            $counterSpyLvl = 0;

                        if(($counterSpyLvl - $spyLvl) >= 2)
                            $colonyPlayerName = $coordinate->planet." - ".$this->player->isWeakOrStrong($colonyPlayer).' '.trans('galaxy.hiddenPlayer', [], $this->player->name);
                    }

                    if(empty($colonyPlayerName) && !$colonyPlayer->npc)
                        $colonyPlayerName = $coordinate->planet." - ".$this->player->isWeakOrStrong($colonyPlayer)." ".$coordinate->colony->name." ".$colonyPlayer->user_name;
                    elseif($colonyPlayer->npc)
                        $colonyPlayerName = $coordinate->planet." - [NPC] ".$coordinate->colony->name." ".$colonyPlayer->user_name;

                    $coordinateList .= $colonyPlayerName."\n";
                }
                else
                    $coordinateList .= $coordinate->planet."\n";

                $ruinFieldString = '';
                foreach(config('stargate.resources') as $resource)
                {
                    if($resource != 'naqahdah' && $coordinate->$resource > 0)
                        $ruinFieldString .= config('stargate.emotes.'.$resource)." ".ucfirst($resource)." ".number_format($coordinate->$resource);
                }
                if(!empty($ruinFieldString))
                    $coordinateList .= trans('galaxy.ruinField', ['resources' => $ruinFieldString], $this->player->lang)."\n";
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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
    }
}
