<?php

namespace App\Commands;

use App\Reminder as ReminderModel;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Str;

class Reminder extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $paginatorMessage;
    public $listner;
    public $closed;

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

            echo PHP_EOL.'Execute Reminder';

            if(!empty($this->args))
            {
                if(Str::startsWith('list', $this->args[0]))
                {
                    $reminderString = "";
                    if(!empty($this->player->reminders))
                    {
                        $now = Carbon::now();
                        foreach($this->player->reminders as $reminder)
                        {
                            $reminderTimeString = $now->diffForHumans($reminder->reminder_date,[
                                'parts' => 3,
                                'short' => true, // short syntax as per current locale
                                'syntax' => CarbonInterface::DIFF_ABSOLUTE
                            ]);
                            $reminderString .= "ID `".$reminder->id."` - ".$reminder->reminder_date." (".$reminderTimeString.") - `".str_replace("**Reminder:** ","",$reminder->reminder)."`\n";
                        }
                    }
                    return "__".trans('reminder.listTitle', [], $this->player->lang)."__:\n\n".$reminderString;
                }
                elseif(Str::startsWith('history', $this->args[0]))
                {
                    if(count($this->args) > 1)
                    {
                        $reminder = ReminderModel::where([['id', (int)$this->args[1]],['player_id',$this->player->id]])->first();
                        if(!is_null($reminder))
                        {
                            if(!is_null($reminder->embed))
                            {
                                $reminderEmbed = json_decode($reminder->embed,true);
                                $newEmbed = $this->discord->factory(Embed::class,$reminderEmbed);
                                $this->message->channel->sendMessage('', false, $newEmbed);
                            }
                            else
                                $this->message->channel->sendMessage($reminder->reminder);
                        }
                        else
                        {
                            return 'Unknown reminder';
                        }
                    }
                    else
                    {
                        $this->page = 1;
                        $this->maxPage = ceil($this->player->remindersHistory->count()/10);
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
                    return;
                }
            }

            if(count($this->args) < 2)
                return trans('reminder.wrongParameter', [], $this->player->lang);

            if(Str::startsWith('remove', $this->args[0]))
            {
                try{
                    $reminderString = "";
                    if(is_numeric($this->args[1]) && $this->args[1] > 0)
                    {
                        $reminder = ReminderModel::find($this->args[1]);
                        if(!is_null($reminder) && $reminder->player->id == $this->player->id)
                        {
                            $reminder->delete();
                            return trans('reminder.removed', [], $this->player->lang);
                        }
                    }
                    return trans("reminder.unknown", [], $this->player->lang);
                }
                catch(\Exception $e)
                {
                    return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
                    return trans('reminder.wrongParameter', [], $this->player->lang);
                }
            }

            try{
                $reason = trim(substr(implode(' ',$this->args),strlen($this->args[0])));
                $reminder = new ReminderModel;
                $reminder->reminder_date = Carbon::now()->add($this->args[0]);
                $reminder->title = trans('reminder.titles.customReminder', [], $this->player->lang);
                $reminder->reminder = "**Reminder:** ".$reason;
                $reminder->player_id = $this->player->id;
                $reminder->save();
                $now = Carbon::now();
                $reminderTimeString = $now->diffForHumans($reminder->reminder_date,[
                    'parts' => 3,
                    'short' => true, // short syntax as per current locale
                    'syntax' => CarbonInterface::DIFF_ABSOLUTE
                ]);
                return trans('reminder.confirm', ['time' => $reminderTimeString, 'reason' => $reason], $this->player->lang);
            }
            catch(\Exception $e)
            {
                return trans('reminder.wrongParameter', [], $this->player->lang);
            }
        }
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

    }

    public function getPage()
    {
        try{
            $displayList = $this->player->remindersHistory->skip(5*($this->page -1))->take(10);

            $description = '';
            foreach($displayList as $reminder)
            {
                if(is_null($reminder->title) || empty($reminder->title))
                    $description .= "ID `".$reminder->id."` - ".$reminder->reminder_date." Unknown (old version)\n";
                else
                    $description .= "ID `".$reminder->id."` - ".$reminder->reminder_date." ".$reminder->title."\n";
            }
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('reminder.listTitle', [], $this->player->lang),
                "description" => $description,
                'fields' => [],
                'footer' => array(
                    //'icon_url'  => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png',
                    'text'  => 'Stargate - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,
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
