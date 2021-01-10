<?php

namespace App\Commands;

use App\Player;
use App\Alliance;
use Illuminate\Support\Str;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;

class Top extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $perPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $topList;
    public $topType;
    public $topAlliance;
    public $closed;

    public function execute()
    {
        if(!is_null($this->player))
        {
            echo PHP_EOL.'Execute Top';

            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            /*if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);*/

            try{
                if(empty($this->args))
                    return trans('top.choice', [], $this->player->lang);

                $this->topAlliance = false;
                if(count($this->args) > 1 && Str::startsWith('alliance', $this->args[1]))
                    $this->topAlliance = true;

                if(Str::startsWith('general', $this->args[0])){
                    $this->topType = 'general';
                    if($this->topAlliance)
                        $this->topList = Alliance::all()->where('id', '!=', 1)->sortByDesc('points_total');
                    else
                        $this->topList = Player::all()->where('npc', 0)->where('id', '!=', 1)->sortByDesc('points_total');
                }
                elseif(Str::startsWith('building', $this->args[0])){
                    $this->topType = 'building';
                    if($this->topAlliance)
                        $this->topList = Alliance::all()->where('id', '!=', 1)->sortByDesc('points_building');
                    else
                        $this->topList = Player::all()->where('npc', 0)->where('id', '!=', 1)->sortByDesc('points_building');
                }
                elseif(Str::startsWith('research', $this->args[0])){
                    $this->topType = 'research';
                    if($this->topAlliance)
                        $this->topList = Alliance::all()->where('id', '!=', 1)->sortByDesc('points_research');
                    else
                        $this->topList = Player::all()->where('npc', 0)->where('id', '!=', 1)->sortByDesc('points_research');
                }
                elseif(Str::startsWith('craft', $this->args[0])){
                    $this->topType = 'craft';
                    if($this->topAlliance)
                        $this->topList = Alliance::all()->where('id', '!=', 1)->sortByDesc('points_craft');
                    else
                        $this->topList = Player::all()->where('npc', 0)->where('id', '!=', 1)->sortByDesc('points_craft');
                }
                elseif(Str::startsWith('military', $this->args[0])){
                    $this->topType = 'military';
                    if($this->topAlliance)
                        $this->topList = Alliance::all()->where('id', '!=', 1)->sortByDesc('points_military');
                    else
                        $this->topList = Player::all()->where('npc', 0)->where('id', '!=', 1)->sortByDesc('points_military');
                }
                /*elseif(Str::startsWith('defence', $this->args[0])){
                    $this->topType = 'defence';
                    if($this->topAlliance)
                        $this->topList = Alliance::all()->where('id', '!=', 1)->sortByDesc('points_defence');
                    else
                        $this->topList = Player::all()->where('npc', 0)->where('id', '!=', 1)->sortByDesc('points_defence');
                }*/
                else
                    return trans('top.choice', [], $this->player->lang);

                $this->closed = false;
                $this->page = 1;
                $this->perPage = 10;
                $this->maxPage = ceil($this->topList->count()/$this->perPage);
                $this->maxTime = time()+180;
                $this->message->channel->sendMessage('', false, $this->getPage())->then(function ($messageSent){
                    $this->paginatorMessage = $messageSent;
                    $this->paginatorMessage->react('⏪')->then(function(){
                        $this->paginatorMessage->react('◀️')->then(function(){
                            $this->paginatorMessage->react('▶️')->then(function(){
                                $this->paginatorMessage->react('⏩')->then(function(){
                                    $this->paginatorMessage->react(config('stargate.emotes.cancel'));
                                });
                            });
                        });
                    });

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
                                elseif($messageReaction->emoji->name == '⏪')
                                {
                                    $this->page = 1;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                {
                                    $this->page--;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                {
                                    $this->page++;
                                    $newEmbed = $this->discord->factory(Embed::class,$this->getPage());
                                    $messageReaction->message->addEmbed($newEmbed);
                                }
                                elseif($messageReaction->emoji->name == '⏩')
                                {
                                    $this->page = $this->maxPage;
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
                    $this->paginatorMessage->createReactionCollector($filter,['time' => config('stargate.maxCollectionTime')]);
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
        $displayList = $this->topList->skip($this->perPage*($this->page -1))->take($this->perPage);
        if($this->topType == 'general')
            $varName = 'points_total';
        else
            $varName = 'points_'.$this->topType;

        $counter = (($this->page-1)*$this->perPage)+1;

        $topList = "";
        foreach($displayList as $listItem)
        {
            if($this->topAlliance)
            {
                $allianceName = "[".$listItem->tag."] ".$listItem->name;
                if(!is_null($this->player->alliance) && $listItem->id == $this->player->alliance->id)
                    $allianceName = "**".$allianceName."**";
                $topList .= $counter.". ".$allianceName." - ".number_format($listItem->$varName)." Points";

                /*if($listItem->{'old_'.$varName} == $listItem->$varName)
                    $topList .= ' (=)';
                elseif($listItem->{'old_'.$varName} < $listItem->$varName)
                    $topList .= ' (+'.number_format(abs($listItem->$varName - $listItem->{'old_'.$varName})).')';
                elseif($listItem->{'old_'.$varName} > $listItem->$varName)
                    $topList .= ' (-'.number_format(abs($listItem->{'old_'.$varName} - $listItem->$varName)).')';*/

                $topList .= "\n";
            }
            else
            {
                $playerName = $listItem->user_name;

                if($listItem->id == $this->player->id)
                    $playerName = "**".$playerName."**";
                $topList .= $counter.". ".$playerName." - ".number_format($listItem->$varName)." Points";

                /*if($listItem->{'old_'.$varName} == $listItem->$varName)
                    $topList .= ' (=)';
                elseif($listItem->{'old_'.$varName} < $listItem->$varName)
                    $topList .= ' (+'.number_format(abs($listItem->$varName - $listItem->{'old_'.$varName})).')';
                elseif($listItem->{'old_'.$varName} > $listItem->$varName)
                    $topList .= ' (-'.number_format(abs($listItem->{'old_'.$varName} - $listItem->$varName)).')';*/

                $topList .= "\n";
            }
            $counter++;
        }
        if(empty($topList))
            $topList = "/";

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => 'Top '.trans('generic.'.$this->topType, [], $this->player->lang),
            "description" => $topList,
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - Page '.$this->page.' / '.$this->maxPage,
            ),
        ];
        return $embed;
    }
}
