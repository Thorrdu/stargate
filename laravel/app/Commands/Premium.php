<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use Discord\Parts\Embed\Embed;
use App\Player;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class Premium extends CommandHandler implements CommandInterface
{
    public $playerGiven;

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
                            $this->player->premium_expiration = Carbon::createFromFormat("Y-m-d H:i:s",$this->player->premium_expiration)->add('1y');
                        else
                            $this->player->premium_expiration = Carbon::now()->add('1y');
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
                            if(!is_null($this->playerGiven))
                            {
                                $upgradeMsg = trans('premium.giveConfirmaton', ['player' => $this->playerGiven->user_name], $this->player->lang);

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
                                    $this->paginatorMessage->createReactionCollector($filter,['limit'=>1])->then(function ($collector){
                                        $messageReaction = $collector->first();
                                        try{
                                            if($messageReaction->emoji->name == config('stargate.emotes.confirm'))
                                            {
                                                $this->player->refresh();
                                                if($this->player->premium > 0)
                                                {
                                                    $this->player->premium--;
                                                    $this->player->save();
                                                    $this->playerGiven->premium++;
                                                    $this->playerGiven->save();
                                                    $this->paginatorMessage->content = str_replace(trans('generic.awaiting', [], $this->player->lang),trans('generic.confirmed', [], $this->player->lang),$this->paginatorMessage->content);
                                                }
                                                else
                                                    $this->paginatorMessage->content = trans('generic.notEnoughResources', ['missingResources' => '1 Premium'], $this->player->lang);
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
