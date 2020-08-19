<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\DiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class HelpCommand extends CommandHandler implements CommandInterface
{
    public $page;
    public $maxPage;
    public $maxTime;
    public $paginatorMessage;
    public $listner;
    public $commandList;
    public $prefix;
    public $lang;
    
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->lang);

            $this->lang = $this->player->lang;
        }
        else
        {
            $this->lang = 'en';
        }

        try{
            $this->prefix = str_replace((string) $this->discord->user, '@'.$this->discord->username, $this->discord->commandClientOptions['prefix']);

            if(empty($this->args) || $this->args[0] == 'list')
            {
                echo PHP_EOL.'Execute Help';

                $this->page = 1;
                $this->maxPage = ceil((count($this->discord->commands)-1)/5);
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

                    $this->listner = function ($messageReaction) {
                        
                        ${'listnerNameHelp'.Str::random(10)} = 55;
                        if($this->maxTime < time()){
                            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->lang), null);
                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                        }

                        if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                        {
                            if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                            {
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->lang), null);
                                $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                            }
                            elseif($messageReaction->emoji->name == '⏪')
                            {
                                $this->page = 1;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '◀️' && $this->page > 1)
                            {
                                $this->page--;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '▶️' && $this->maxPage > $this->page)
                            {
                                $this->page++;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                            elseif($messageReaction->emoji->name == '⏩')
                            {
                                $this->page = $this->maxPage;
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id,'',$this->getPage());
                                $this->paginatorMessage->deleteReaction('id', urlencode($messageReaction->emoji->name), $messageReaction->user_id);
                            }
                        }
                    };
                    $this->discord->on('MESSAGE_REACTION_ADD', $this->listner);
                });
            }
            else
            {

                $commandString = implode(' ', $this->args);
                $command = $this->discord->getCommand($commandString);
    
                if (is_null($command)) {
                    return "The command {$commandString} does not exist...";
                }
    
                $help = $command->getHelp($this->prefix);
    
                $embed = [
                    'author' => [
                        'name' => $this->discord->commandClientOptions['name'],
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    "title" => 'Help: '.$help['command'],
                    "description" => !empty($help['longDescription'])?$help['longDescription']:trans('help.'.$help['command'].'.description', [], $this->lang),
                    'fields' => [],
                    'footer' => array(
                        'text'  => $this->discord->commandClientOptions['name'],
                    ),
                ];
    
                if(!empty($help['usage']))
                {
                    $embed['fields'][] = array(
                        'name' => 'Usage',
                        'value' => "``".trans('help.'.$help['command'].'.usage', [], $this->lang)."``",
                        'inline' => true
                    );
                }
    
                if(!empty($this->discord->aliases))
                {
                    $aliasesString = "";
                    foreach ($this->discord->aliases as $alias => $command) {
                        if ($command != $commandString) {
                            continue;
                        }
    
                        $aliasesString .= "{$alias}\r\n";
                    }
                    $embed['fields'][] = array(
                        'name' => 'Aliases',
                        'value' => $aliasesString,
                        'inline' => true
                    );
                }
    
                $this->message->channel->sendMessage('', false, $embed);
    
                return;
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
            return $e->getMessage();
        }
        

        return false;
    }

    public function getPage()
    {
        try{

            $displayList = array_slice($this->discord->commands,(5*($this->page -1))+1,5);
            $embed = [
                'author' => [
                    'name' => $this->discord->commandClientOptions['name'],
                    'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                ],
                "title" => $this->discord->commandClientOptions['name'].' Help',
                "description" => trans('help.mainHelp', [], $this->lang)."\n----------------------------",
                'fields' => [],
                'footer' => array(
                    'text'  => $this->discord->commandClientOptions['name'].' - '.trans('generic.page', [], $this->lang).' '.$this->page.' / '.$this->maxPage,

                ),
            ];

            foreach($displayList as $command)
            {
                $help = $command->getHelp($this->prefix);
                if($help['command'] != 'help')
                {
                    $embed['fields'][] = array(
                        'name' => $this->prefix.$help['command'],
                        'value' => trans('help.'.$help['command'].'.description', [], $this->lang),
                        'inline' => false
                    );
                }
            }
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }
        return $embed;
    }

}
