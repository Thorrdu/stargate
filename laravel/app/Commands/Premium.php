<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\myDiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;
use App\Player;
use App\Trade;
use App\TradeResource;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class Premium extends CommandHandler implements CommandInterface
{
    public $playerGiven;
    public $qtyToGive;

    public function execute()
    {
        if(!is_null($this->player))
        {
            try{

                if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

                if($this->player->captcha)
                    return trans('generic.captchaMessage',[],$this->player->lang);

                if(!is_null($this->player->vacation))
                    return trans('profile.vacationMode', [], $this->player->lang);

                if(!empty($this->args) && Str::startsWith('use', $this->args[0]))
                {
                    if($this->player->premium > 0)
                    {
                        $this->player->premium--;

                        if(!is_null($this->player->premium_expiration))
                            $this->player->premium_expiration = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->premium_expiration)->add('31d');
                        else
                            $this->player->premium_expiration = Carbon::now()->add('31d');
                        $this->player->save();

                        foreach($this->player->colonies as $colony)
                        {
                            $colony->calcProd(); //reload Prods
                            $colony->save();
                        }

                        $now = Carbon::now();
                        $premiumExpiration = $now->diffForHumans($this->player->premium_expiration,[
                            'parts' => 3,
                            'short' => true,
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);

                        return trans('premium.activated', ['expiration' => $premiumExpiration], $this->player->lang);
                    }
                    else
                        return trans('generic.notEnoughResources', ['missingResources' => '1 Premium'], $this->player->lang);
                }
                elseif(!empty($this->args) && Str::startsWith('give', $this->args[0]))
                {
                    if($this->player->premium > 0)
                    {
                        if(count($this->args) < 2)
                            return trans('generic.missingArgs',[],$this->player->lang);

                        if(preg_match("/[0-9]{18}/", $this->args[1], $playerMatch))
                        {
                            $this->playerGiven = Player::where('user_id', $playerMatch[0])->first();
                            if(!is_null($this->playerGiven) && $this->playerGiven->user_id != $this->player->user_id)
                            {
                                $this->qtyToGive = 1;
                                if(isset($this->args[2]) && (int)$this->args[2] > 0)
                                    $this->qtyToGive = (int)$this->args[2];

                                if($this->player->trade_ban)
                                    return trans('stargate.trade_ban', [], $this->player->lang);
                                elseif($this->playerGiven->ban)
                                    return trans('stargate.playerTradeBan', [], $this->player->lang);
                                elseif($this->player->user_id != config('stargate.ownerId'))
                                {
                                    $activeTradeCheck = Trade::where([["player_id_source", $this->player->id], ["player_id_dest", '!=', $this->playerGiven->id], ["active", true]])
                                                        ->orWhere([["player_id_dest", $this->player->id], ["player_id_source", '!=', $this->playerGiven->id], ["active", true]])->count();

                                    if($activeTradeCheck > 0)
                                        return trans('trade.youAlreadyHaveActiveTrade', [], $this->player->lang);
                                    else
                                    {
                                        $playerActiveTradeCheck = Trade::where([["player_id_source", '!=', $this->player->id], ["player_id_dest", $this->playerGiven->id], ["active", true]])
                                        ->orWhere([["player_id_dest", '!=', $this->player->id], ["player_id_source", $this->playerGiven->id], ["active", true]])->count();

                                        if($playerActiveTradeCheck > 0)
                                            return trans('trade.playerHasActiveTrade', [], $this->player->lang);
                                    }
                                }

                                $upgradeMsg = trans('premium.giveConfirmaton', ['player' => $this->playerGiven->user_name,'qty' => $this->qtyToGive], $this->player->lang);

                                $this->maxTime = time()+180;
                                $this->message->channel->sendMessage($upgradeMsg)->then(function ($messageSent){

                                    $this->paginatorMessage = $messageSent;
                                    $this->paginatorMessage->react(config('stargate.emotes.confirm'))->then(function(){
                                        $this->paginatorMessage->react(config('stargate.emotes.cancel'))->then(function(){
                                        });
                                    });

                                    $filter = function($messageReaction){
                                        return $messageReaction->user_id == $this->player->user_id;
                                    };
                                    $this->paginatorMessage->createReactionCollector($filter,['limit' => 1,'time' => config('stargate.maxCollectionTime')])->then(function ($collector){
                                        $messageReaction = $collector->first();
                                        try{
                                            if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                            {
                                                $this->player->refresh();

                                                $activeTradeCheck = Trade::where([["player_id_source", $this->player->id], ["player_id_dest", '!=', $this->playerGiven->id], ["active", true]])
                                                                    ->orWhere([["player_id_dest", $this->player->id], ["player_id_source", '!=', $this->playerGiven->id], ["active", true]])->count();

                                                if($this->player->user_id != config('stargate.ownerId'))
                                                {
                                                    if($activeTradeCheck > 0)
                                                        return trans('trade.youAlreadyHaveActiveTrade', [], $this->player->lang);
                                                    else
                                                    {
                                                        $playerActiveTradeCheck = Trade::where([["player_id_source", '!=', $this->player->id], ["player_id_dest", $this->playerGiven->id], ["active", true]])
                                                        ->orWhere([["player_id_dest", '!=', $this->player->id], ["player_id_source", $this->playerGiven->id], ["active", true]])->count();

                                                        if($playerActiveTradeCheck > 0)
                                                            return trans('trade.playerHasActiveTrade', [], $this->player->lang);
                                                    }
                                                }

                                                if( $this->player->premium >= $this->qtyToGive )
                                                {
                                                    $this->player->premium -= $this->qtyToGive ;
                                                    $this->player->save();
                                                    $this->playerGiven->premium += $this->qtyToGive ;
                                                    $this->playerGiven->save();
                                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);

                                                    if($this->player->user_id != config('stargate.ownerId'))
                                                    {
                                                        $tradeLogCheck = Trade::where([['player_id_dest',$this->playerGiven->id], ['player_id_source',$this->player->id], ['active', true]])
                                                                                ->orWhere([['player_id_source',$this->playerGiven->id], ['player_id_dest',$this->player->id], ['active', true]])->first();

                                                        if(!is_null($tradeLogCheck))
                                                        {
                                                            $tradeLog = $tradeLogCheck;
                                                            $tradePlayer = '';
                                                            if($this->player->id == $tradeLog->player_id_source)
                                                                $tradePlayer = 1;
                                                            else
                                                                $tradePlayer = 2;
                                                        }
                                                        else
                                                        {
                                                            $tradeLog = new Trade;
                                                            $tradeLog->player_id_source = $this->player->id;
                                                            $tradeLog->player_id_dest = $this->playerGiven->id;
                                                            $tradeLog->trade_value_player1 = 0;
                                                            $tradeLog->trade_value_player2 = 0;
                                                            $tradeLog->save();
                                                            $tradePlayer = 1;
                                                        }

                                                        $tradeResourceExist = $tradeLog->tradeResources->filter(function ($value) use($tradePlayer){
                                                            return $value->resource == 'premium' && $value->player == $tradePlayer;
                                                        });
                                                        if($tradeResourceExist->count() > 0)
                                                        {
                                                            $tradeResource = $tradeResourceExist->first();
                                                            $tradeResource->quantity += $this->qtyToGive;
                                                        }
                                                        else
                                                        {
                                                            $tradeResource = new TradeResource();
                                                            $tradeResource->player = $tradePlayer;
                                                            $tradeResource->trade_id = $tradeLog->id;
                                                            $tradeResource->resource = 'premium';
                                                            $tradeResource->quantity = $this->qtyToGive;
                                                        }
                                                        $tradeResource->trade_value = 0;
                                                        $tradeResource->save();
                                                        $tradeLog->setTradeValue();
                                                        $tradeLog->save();
                                                    }

                                                }
                                                else
                                                    $this->paginatorMessage->content = trans('generic.notEnoughResources', ['missingResources' => ($this->qtyToGive-$this->player->premium).' Premium'], $this->player->lang);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
                                            }
                                            elseif($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                            {
                                                $this->paginatorMessage->content = trans('generic.cancelled', [], $this->player->lang);
                                                $this->paginatorMessage->channel->messages->save($this->paginatorMessage);
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
                                return trans('generic.unknownPlayer',[],$this->player->lang);
                        }
                        else
                            return trans('generic.unknownPlayer',[],$this->player->lang);
                    }
                    else
                        return trans('generic.notEnoughResources', ['missingResources' => '1 Premium'], $this->player->lang);
                }
                else
                {
                    $premiumMessage = "";
                    if(!is_null($this->player->premium_expiration))
                    {
                        $now = Carbon::now();

                        $premiumExpirationDate = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->premium_expiration);
                        $premiumExpiration = $now->diffForHumans($premiumExpirationDate,[
                            'parts' => 3,
                            'short' => true,
                            'syntax' => CarbonInterface::DIFF_ABSOLUTE
                        ]);
                        $premiumMessage .= trans("premium.premiumStatus", ['premiumStatus' => trans("generic.active", [], $this->player->lang)], $this->player->lang);
                        $premiumMessage .= "\n".trans("premium.premiumExpires", ['expiration' => $premiumExpiration], $this->player->lang);
                    }
                    else
                        $premiumMessage .= trans("premium.premiumStatus", ['premiumStatus' => trans("generic.inactive", [], $this->player->lang)], $this->player->lang);

                    $premiumMessage .= "\n".trans("premium.havingPremium", ['premium' => $this->player->premium ], $this->player->lang);
                    if($this->player->premium > 0)
                        $premiumMessage .= "\n".trans("premium.howTo", [], $this->player->lang);
                    else
                        $premiumMessage .= "\n".trans("premium.howGetPrem", [], $this->player->lang);


                    return $premiumMessage;
                }

            }
            catch(\Exception $e)
            {
                return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');
        return false;
    }
}
