<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Ship;
use App\Building;
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

                                if($this->player->trade_ban)
                                {
                                    $trade->playerSource->trade_ban = false;
                                    $trade->playerSource->trade_extend = null;
                                    $trade->playerSource->save();
                                    $trade->playerDest->trade_ban = false;
                                    $trade->playerDest->trade_extend = null;
                                    $trade->playerDest->save();
                                }
                            }
                            else
                            {
                                return trans('trade.', [], $this->player->lang);
                            }
                        }

                        if(isset($this->args[1]) && Str::startsWith('extend', $this->args[1]))
                        {
                            if($trade->extended)
                                return trans('trade.alreadyExtended', [], $this->player->lang);
                                //une extention de temps à déjà été accordée

                            $tradeTime = Carbon::createFromFormat("Y-m-d H:i:s",$trade->created_at);
                            if(abs($tradeTime->diffInHours(Carbon::now())) < 72)
                                return trans('trade.extentionNotRequired', [], $this->player->lang);

                            $trade->extended = true;
                            $trade->save();

                            $trade->playerSource->trade_extend = Carbon::now()->add('48h');
                            $trade->playerSource->save();
                            $trade->playerDest->trade_extend = Carbon::now()->add('48h');
                            $trade->playerDest->save();

                            return trans('trade.extentionGranted', [], $this->player->lang);
                        }

                        $now = Carbon::now();
                        $tradeCreation = Carbon::createFromFormat("Y-m-d H:i:s",$trade->created_at);
                        $closingDate = $tradeCreation->add('72h');
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
                            $tradeEnding = $now->diffForHumans($closingDate,[
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

                        $embed = [
                            'author' => [
                                'name' => $this->player->user_name,
                                'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                            ],
                            "title" => trans('trade.tradeDetail', ['tradeID' => $trade->id], $this->player->lang),
                            "description" => trans('trade.tradeInfos', ['player1' => $trade->playerSource->user_name.' ( '.number_format($trade->playerSource->points_total).' Points )', 'player2' => $trade->playerDest->user_name.' ( '.number_format($trade->playerDest->points_total).' Points )', 'time' => $tradeEnding, 'status' => $status, 'warning' => $warning], $this->player->lang),
                            'fields' => [],
                            'footer' => array(
                                'text'  => 'Stargate - '.trans('trade.tradeDetail', ['tradeID' => $trade->id], $this->player->lang),
                            ),
                        ];

                        $player1TradeString = trans('trade.tradeValue', ['totalValue' => number_format($trade->trade_value_player1)], $this->player->lang)."\n";
                        $player2TradeString = trans('trade.tradeValue', ['totalValue' => number_format($trade->trade_value_player2)], $this->player->lang)."\n";

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
            $closingDate = $tradeCreation->add('72h');
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
                $tradeEnding = $now->diffForHumans($tradeCreation->add('72h'),[
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
}
