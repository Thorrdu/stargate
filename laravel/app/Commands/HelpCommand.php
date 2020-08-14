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
    
    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->player->lang);

            if($this->player->captcha)
                return trans('generic.captchaMessage',[],$this->player->lang);
        }

        $this->prefix = str_replace((string) $this->discord->user, '@'.$this->discord->username, $this->discord->commandClientOptions['prefix']);

        try{
            if(empty($this->args) || $this->args[0] == 'list')
            {
                echo PHP_EOL.'Execute Build';

                $embed = [
                    'author' => [
                        'name' => $this->discord->commandClientOptions['name'],
                        'icon_url' => $this->discord->client->avatar
                    ],
                    "title" => $this->discord->commandClientOptions['name'].'\'s Help',
                    "description" => $this->discord->commandClientOptions['description']."\n\nRun `{$this->prefix}help` command to get more information about a specific command.\n----------------------------",
                    'fields' => [],
                    'footer' => array(
                        'text'  => $this->discord->commandClientOptions['name'],
                    ),
                ];
                
                $this->page = 1;
                $this->maxPage = ceil(count($this->discord->commands)/5);
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
                        if($this->maxTime < time()){
                            $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->name), null);
                            $this->discord->removeListener('MESSAGE_REACTION_ADD',$this->listner);
                        }

                        if($messageReaction->message_id == $this->paginatorMessage->id && $messageReaction->user_id == $this->player->user_id)
                        {
                            if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                            {
                                $this->paginatorMessage->channel->editMessage($this->paginatorMessage->id, trans('generic.closedList', [], $this->player->name), null);
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
                        'icon_url' => $this->discord->client->user->avatar
                    ],
                    "title" => $help['command'].'\'s Help',
                    "description" => !empty($help['longDescription'])?$help['longDescription']:$help['description'],
                    'fields' => [],
                    'footer' => array(
                        'text'  => $this->discord->commandClientOptions['name'],
                    ),
                ];
    
                if(!empty($help['usage']))
                {
                    $embed['fields'][] = array(
                        'name' => 'Usage',
                        'value' => "``".$help['usage']."``",
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
    
                if(!empty($help['subCommandsHelp']))
                {
                    foreach($help['subCommandsHelp'] as $subCommandHelp) {
                        $embed['fields'][] = array(
                            'name' => $subCommandHelp['command'],
                            'value' => $subCommandHelp['description'],
                            'inline' => true
                        );
                    }
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
        $displayList = $this->discord->commands->skip(5*($this->page -1))->take(5);

        $embed = [
            'author' => [
                'name' => $this->discord->commandClientOptions['name'],
                'icon_url' => $this->discord->client->avatar
            ],
            "title" => $this->discord->commandClientOptions['name'].'\'s Help',
            "description" => $this->discord->commandClientOptions['description']."\n\nRun `{$this->prefix}help` command to get more information about a specific command.\n----------------------------",
            'fields' => [],
            'footer' => array(
                'text'  => $this->discord->commandClientOptions['name'].' - '.trans('generic.page', [], $this->player->lang).' '.$this->page.' / '.$this->maxPage,

            ),
        ];

        foreach($displayList as $command)
        {

            $help = $command->getHelp($this->prefix);
            $embed['fields'][] = array(
                'name' => $help['command'],
                'value' => $help['description'],
                'inline' => true
            );

        }

        return $embed;
    }

}
