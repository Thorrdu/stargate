<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\myDiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Ship;
use App\Building;
use App\Pact;
use App\Technology;
use App\Trade;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Str;

class TradeCommand extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $tradeList;
    public $pactList;

    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            if(!is_null($this->player->vacation))
                return trans('profile.vacationMode',[],$this->player->lang);

            $this->player->activeColony->checkColony();
            $this->player->refresh();

            try{
                echo PHP_EOL.'Execute Trade';

                if(empty($this->args) || Str::startsWith('list', $this->args[0]))
                {
                    $this->tradeList = Trade::where([["player_id_source", $this->player->id], ["active", true]])->orWhere([["player_id_dest", $this->player->id], ["active", true]])->get();

                    if($this->tradeList->count() == 0)
                    {
                        return trans('trade.emptyList', [], $this->player->lang);
                    }

                    $this->closed = false;
                    $this->page = 1;
                    $this->maxPage = ceil($this->tradeList->count()/5);
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
                        $this->paginatorMessage->createReactionCollector($filter);
                    });
                }
                elseif(Str::startsWith($this->args[0], 'pact'))
                {
                    if(count($this->args) < 2)
                        return trans('generic.wrongParameter', [] , $this->player->lang);

                    if(Str::startsWith($this->args[1], 'list'))
                    {
                        $this->pactList = Pact::Where('player_1_id', $this->player->id)->orWhere('player_2_id', $this->player->id)->get();

                        if(count($this->pactList) == 0)
                        {
                            return trans('trade.emptyPacts', [], $this->player->lang);
                        }

                        $this->closed = false;
                        $this->page = 1;
                        $this->maxPage = ceil($this->pactList->count()/5);
                        $this->maxTime = time()+180;
                        $this->message->channel->sendMessage('', false, $this->getPactPage())->then(function ($messageSent){
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
                                        }
                                        elseif($messageReaction->emoji->name == '⏪')
                                        {
                                            $this->page = 1;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPactPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                                        {
                                            $this->page--;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPactPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                                        {
                                            $this->page++;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPactPage());
                                            $messageReaction->message->addEmbed($newEmbed);
                                        }
                                        elseif($messageReaction->emoji->name == '⏩')
                                        {
                                            $this->page = $this->maxPage;
                                            $newEmbed = $this->discord->factory(Embed::class,$this->getPactPage());
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
                            $this->paginatorMessage->createReactionCollector($filter);
                        });


                        return;
                    }

                    if(preg_match("/[0-9]{18}/", $this->args[1], $playerMatch))
                    {
                        $playerInvited = Player::where('user_id', $playerMatch[0])->first();
                        if(!is_null($playerInvited))
                        {
                            $pactExists = Pact::Where([['player_1_id', $this->player->id], ['player_2_id', $playerInvited->id]])->orWhere([['player_2_id', $this->player->id], ['player_1_id', $playerInvited->id]])->get()->first();

                            if(isset($this->args[2]) && Str::startsWith($this->args[2], 'cancel'))
                            {
                                if(is_null($pactExists))
                                    return trans('trade.noPactWithThisPlayer', [] , $this->player->lang);

                                //Check si trade actif non fair
                                $activeTrade = Trade::where([["player_id_source", $this->player->id], ["player_id_dest", $playerInvited->id], ["active", true]])->orWhere([["player_id_dest", $this->player->id], ["player_id_source", $playerInvited->id], ["active", true]])->get()->first();
                                if(!is_null($activeTrade))
                                    return trans('trade.cantCancelWithActiveTrade', [] , $this->player->lang);

                                $pactCancelMsg = trans('trade.pactCancelConfirm', [
                                    'player_2_name' => $playerInvited->user_name
                                ], $this->player->lang);

                                $this->maxTime = time()+180;
                                $this->message->channel->sendMessage($pactCancelMsg)->then(function ($messageSent) use($playerInvited){

                                    $this->closed = false;
                                    $this->paginatorMessage = $messageSent;
                                    $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                        $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                        });
                                    });

                                    $filter = function($messageReaction) use($playerInvited){
                                        return $messageReaction->user_id == $this->player->user_id;
                                    };
                                    $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($playerInvited){
                                        $messageReaction = $collector->first();
                                        try{
                                            if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                            {
                                                $pactExists = Pact::Where([['player_1_id', $this->player->id], ['player_2_id', $playerInvited->id]])->orWhere([['player_2_id', $this->player->id], ['player_1_id', $playerInvited->id]])->get()->first();
                                                if(!is_null($pactExists))
                                                {
                                                    $pactExists->delete();
                                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                                    $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                    $this->closed = true;
                                                }
                                                return;
                                            }
                                            elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                            {
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                $this->closed = true;
                                            }
                                            $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                        }
                                        catch(\Exception $e)
                                        {
                                            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                        }
                                    });
                                });
                                return;
                            }

                            if(!is_null($pactExists))
                                return trans('trade.pactAlreadyExists', [] , $this->player->lang);

                            $pactInviteMsg = trans('trade.pactConfirm', [
                                'player_1_id' => $this->player->user_id,
                                'player_2_id' => $playerInvited->user_id,
                            ], $this->player->lang);

                            $this->maxTime = time()+180;
                            $this->message->channel->sendMessage($pactInviteMsg)->then(function ($messageSent) use($playerInvited){

                                $this->closed = false;
                                $this->paginatorMessage = $messageSent;
                                $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                    $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                    });
                                });

                                $filter = function($messageReaction) use($playerInvited){
                                    return $messageReaction->user_id == $playerInvited->user_id;
                                };
                                $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector) use($playerInvited){
                                    $messageReaction = $collector->first();
                                    try{
                                        if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                        {
                                            $pactExists = Pact::Where([['player_1_id', $this->player->id], ['player_2_id', $playerInvited->id]])->orWhere([['player_2_id', $this->player->id], ['player_1_id', $playerInvited->id]])->get()->first();
                                            if(!is_null($pactExists))
                                            {
                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                $this->closed = true;
                                            }
                                            else
                                            {
                                                /**NEW PACT */

                                                $pact = new Pact;
                                                $pact->player_1_id = $this->player->id;
                                                $pact->player_2_id = $playerInvited->id;
                                                $pact->save();

                                                $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                                $this->closed = true;
                                            }
                                            return;
                                        }
                                        elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                        {
                                            $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.cancelled', [], $this->player->lang),$this->paginatorMessage->content);
                                            $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            $this->closed = true;
                                        }
                                        $messageReaction->message->deleteReaction(Message::REACT_DELETE_ALL);
                                    }
                                    catch(\Exception $e)
                                    {
                                        echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                                    }
                                });
                            });
                        }
                        else
                        {
                            return trans('generic.unknownPlayer', [] , $this->player->lang);
                        }
                    }
                    else
                        return trans('generic.wrongParameter', [] , $this->player->lang);
                }
                elseif(Str::startsWith($this->args[0], 'ratio'))
                {
                    $this->message->channel->sendMessage("1 Iron\n1 Gold = 1.5 Iron\n1 Quartz = 3 Iron\n1 Naqahdah = 3 Iron\n".trans('generic.e2pz', [], $this->player->lang)." = 10.000 Iron");
                }
                else
                {
                    if((int)$this->args[0] <= 0)
                        return trans('generic.wrongParameter', [] , $this->player->lang);

                    $trade = Trade::find((int)$this->args[0]);
                    if(!is_null($trade))
                    {
                        if(!in_array($this->player->id,array($trade->playerSource->id,$trade->playerDest->id)) && $this->player->user_id != config('stargate.ownerId'))
                            return trans('trade.notYourTrade', [], $this->player->lang);

                        if(isset($this->args[1]) && Str::startsWith('close', $this->args[1]))
                        {
                            if($trade->getFairness())
                            {
                                $trade->active = false;
                                $trade->save();
                                return trans('trade.closed', [], $this->player->lang);
                            }
                            else
                            {
                                return trans('trade.status.unbalanced', [], $this->player->lang);
                            }
                        }

                        $now = Carbon::now();
                        $tradeCreation = Carbon::createFromFormat("Y-m-d H:i:s",$trade->created_at);
                        $closingDate = $tradeCreation->add('24h');

                        if($trade->active && $closingDate->isPast() && $trade->getFairness())
                        {
                            $trade->active = false;
                            $trade->save();
                        }

                        if($trade->active && !$trade->getFairness())
                        {
                            $tradeEnding = trans('trade.awaitBalancing', [], $this->player->lang);
                        }
                        elseif($trade->active)
                        {
                            $tradeEnding = $now->diffForHumans($closingDate,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                        }
                        else
                        {
                            $tradeEnding = trans('generic.closed', [], $this->player->lang);
                        }

                        $warning = '';
                        if($trade->getFairness())
                            $status = trans('trade.status.balanced', [], $this->player->lang);
                        else
                        {
                            $status = trans('trade.status.unbalanced', [], $this->player->lang);
                            $warning = trans('trade.warning', [], $this->player->lang);
                        }

                        $embed = [
                            'author' => [
                                'name' => $this->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            "title" => trans('trade.tradeDetail', ['tradeID' => $trade->id], $this->player->lang),
                            "description" => trans('trade.tradeInfos', [
                                                'player1' => $trade->playerSource->user_name.' ( '.number_format($trade->playerSource->points_total).' Points )',
                                                'player2' => $trade->playerDest->user_name.' ( '.number_format($trade->playerDest->points_total).' Points )',
                                                'time' => $tradeEnding,
                                                'status' => $status,
                                                'warning' => $warning
                                            ], $this->player->lang),
                            'fields' => [],
                            'footer' => array(
                                'text'  => 'Stargate - '.trans('trade.tradeDetail', ['tradeID' => $trade->id], $this->player->lang),
                            ),
                        ];

                        $tradeTotalValue = $trade->trade_value_player1 + $trade->trade_value_player2;
                        $player1TradeString = trans('trade.tradeValue', ['totalValue' => number_format($trade->trade_value_player1)."\n".number_format(($trade->trade_value_player1/$tradeTotalValue)*100,2).'%'], $this->player->lang)."\n";
                        $player2TradeString = trans('trade.tradeValue', ['totalValue' => number_format($trade->trade_value_player2)."\n".number_format(($trade->trade_value_player2/$tradeTotalValue)*100,2).'%'], $this->player->lang)."\n";

                        foreach($trade->tradeResources as $tradeResource)
                        {
                            if(!is_null($tradeResource->unit))
                                ${'player'.$tradeResource->player.'TradeString'} .= trans('craft.'.$tradeResource->unit->slug.'.name', [], $this->player->lang).': '.number_format($tradeResource->quantity)." ( ".number_format($tradeResource->trade_value)." )\n";
                            else
                                ${'player'.$tradeResource->player.'TradeString'} .= config('stargate.emotes.'.strtolower($tradeResource->resource))." ".ucfirst($tradeResource->resource).': '.number_format($tradeResource->quantity)." ( ".number_format($tradeResource->trade_value)." )\n";
                        }

                        $embed['fields'][] = array(
                            'name' => $trade->playerSource->user_name,
                            'value' => $player1TradeString,
                            'inline' => true
                        );
                        $embed['fields'][] = array(
                            'name' => $trade->playerDest->user_name,
                            'value' => $player2TradeString,
                            'inline' => true
                        );

                        $newEmbed = $this->discord->factory(Embed::class,$embed);
                        $this->message->channel->sendMessage('', false, $newEmbed);
                    }
                    else
                        return trans('trade.unknownTrade', [], $this->player->lang);
                }
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
        $displayList = $this->tradeList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('trade.tradeList', [], $this->player->lang),
            "description" => trans('trade.howTo', [], $this->player->lang),
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        foreach($displayList as $trade)
        {
            $now = Carbon::now();
            $tradeCreation = Carbon::createFromFormat("Y-m-d H:i:s",$trade->created_at);
            $closingDate = $tradeCreation->add('24h');
            if($closingDate->isPast())
            {

                if($trade->extended && !is_null($this->player->trade_extend))
                {

                    $extendDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->trade_extend);
                    if($extendDate->isPast())
                        $tradeEnding = trans('generic.closed', [], $this->player->lang);
                    else
                    {
                        $tradeEnding = $now->diffForHumans($extendDate,[
                            'parts' => 3,
                            'short' => true, // short syntax as per current locale
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                    }
                }
                else
                    $tradeEnding = trans('generic.closed', [], $this->player->lang);
            }
            else
            {
                $tradeEnding = $now->diffForHumans($tradeCreation->add('24h'),[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
            }

            $warning = '';
            if($trade->getFairness())
                $status = trans('trade.status.balanced', [], $this->player->lang);
            else
            {
                $status = trans('trade.status.unbalanced', [], $this->player->lang);
                $warning = trans('trade.warning', [], $this->player->lang);
            }

            $embed['fields'][] = array(
                'name' => trans('trade.tradeDetail', ['tradeID' => $trade->id], $this->player->lang),
                'value' => trans('trade.tradeInfos', ['player1' => $trade->playerSource->user_name, 'player2' => $trade->playerDest->user_name, 'time' => $tradeEnding, 'status' => $status, 'warning' => $warning], $this->player->lang),
                'inline' => true
            );
        }

        return $embed;
    }

    public function getPactPage()
    {
        $displayList = $this->pactList->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->player->user_name,
                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
            ],
            "title" => trans('trade.pactList', [], $this->player->lang),
            "description" => "",
            'fields' => [],
            'footer' => array(
                'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
            ),
        ];

        $pactString = "";
        foreach($displayList as $pact)
        {
            $this->pactList = Pact::Where('player_1_id', $this->player->id)->orWhere('player_2_id', $this->player->id)->get();

            if($this->player->id != $pact->player_1_id)
                $pactString .= "\n".$pact->player1->user_name;
            else
                $pactString .= "\n".$pact->player2->user_name;
        }

        $embed['fields'][] = array(
            'name' => trans('trade.pactPlayers', [], $this->player->lang),
            'value' => $pactString,
            'inline' => true
        );

        return $embed;
    }
}
