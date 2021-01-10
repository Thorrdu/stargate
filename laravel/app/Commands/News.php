<?php

namespace App\Commands;

use App\Alert;
use App\Reminder as ReminderModel;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Illuminate\Support\Str;

class News extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $paginatorMessage;
    public $listner;
    public $closed;
    public $newsList;

    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);

            echo PHP_EOL.'Execute News';

            $this->newsList = Alert::where("published",true)->orderBy('publication_date','desc')->get()->take(100);
            $this->page = 1;
            $this->maxPage = ceil($this->newsList->count()/10);
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
        else
            return trans('generic.start',[],'en')." / ".trans('generic.start',[],'fr');

    }

    public function getPage()
    {
        try{
            $displayList = $this->newsList->skip(5*($this->page -1))->take(5);

            $description = '';




            foreach($displayList as $alert)
            {
                $description .= trans('alert.'.$alert->type.'.title', [], $this->player->lang)."\n".
                                $alert->{'news_'.$this->player->lang}."\n\n";
            }
            $embed = [
                'author' => [
                    'name' => $this->player->user_name,
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => trans('alert.listTitle', [], $this->player->lang),
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
