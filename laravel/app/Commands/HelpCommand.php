<?php

namespace App\Commands;

use Illuminate\Database\Eloquent\Model;
use Discord\myDiscordCommandClient;
use \Discord\Parts\Channel\Message as Message;
use App\Player;
use App\Building;
use App\Technology;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Discord\Parts\Embed\Embed;
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
    public $closed;

    public function execute()
    {
        if(!is_null($this->player))
        {
            if($this->player->ban)
                return trans('generic.banned',[],$this->lang);

            $this->lang = $this->player->lang;
        }
        else
        {
            $this->lang = 'en';
        }

        try{

            $this->prefix = $this->discord->commandClientOptions['prefix'];
            if(!is_null($this->message->channel->guild_id))
            {
                $guildConfig = config('stargate.guilds.'.$this->message->channel->guild_id);
                if(!is_null($guildConfig))
                    $this->prefix = $guildConfig['prefix'];
            }

            //$this->prefix = str_replace((string) $this->discord->user, '@'.$this->discord->username, $this->discord->commandClientOptions['prefix']);

            if(empty($this->args) || $this->args[0] == 'list')
            {
                echo PHP_EOL.'Execute Help';

                $embed = [
                    'author' => [
                        'name' => $this->discord->commandClientOptions['name'],
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    "title" => $this->prefix.' Help',
                    "description" => trans('help.mainHelp', ['prefix' => $this->prefix], $this->lang)."\n----------------------------",
                    'fields' => [],
                    'footer' => array(
                        'text'  => $this->discord->commandClientOptions['name'],

                    ),
                ];

                $gameCommandString = $utilityCommandString = $adminCommandString = '';

                foreach($this->discord->commands as $command)
                {
                    $help = $command->getHelp($this->prefix);

                    if(!strstr($help['command'],'help'))
                    {
                        if(!empty(${$command->group.'CommandString'}))
                            ${$command->group.'CommandString'} .= ' ';
                        ${$command->group.'CommandString'} .= '`'.str_replace($this->prefix,'',$help['command']).'`';
                    }
                }

                $embed['fields'][] = array(
                    'name' => 'Game',
                    'value' => $gameCommandString,
                    'inline' => false
                );

                $embed['fields'][] = array(
                    'name' => 'Utility',
                    'value' => $utilityCommandString,
                    'inline' => false
                );

                if(!is_null($this->player) && $this->player->user_id == config('stargate.ownerId'))
                {
                    $embed['fields'][] = array(
                        'name' => 'Admin',
                        'value' => $adminCommandString,
                        'inline' => false
                    );
                }

                $this->message->channel->sendMessage('', false, $embed);

                return;

                $this->closed = false;
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

                    $filter = function($messageReaction){
                        if($messageReaction->user_id != $this->player->user_id || $this->closed == true)
                            return false;

                        if($messageReaction->user_id == $this->player->user_id)
                        {
                            try{
                                if($messageReaction->emoji->name == config('stargate.emotes.cancel'))
                                {
                                    $newEmbed = $this->discord->factory(Embed::class,['title' => trans('generic.closedList', [], $this->lang)]);
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
                    return;
                });
            }
            else
            {
                $commandString = implode(' ', $this->args);
                $command = $this->discord->getCommand($commandString);

                if (is_null($command)) {

                    $commandsFound = [];
                    $commandKeys = array_keys($this->discord->commands);

                    foreach($commandKeys as $key)
                    {
                        $len = strlen($commandString);
                        if((substr($key, 0, $len) === $commandString))
                        {
                            if($key != 'ban' || $message->author->id == config('stargate.ownerId'))
                                $commandsFound[] = $key;
                        }
                    }

                    if(array_key_exists($commandString, $this->discord->aliases)) {
                        $command = $this->discord->commands[$this->discord->aliases[$commandString]];
                    }elseif(empty($commandsFound))
                    {
                        return "The command {$commandString} does not exist...";
                    }
                    elseif(count($commandsFound) > 1)
                    {
                        //var_dump($commandsFound);
                        $returnString = '';
                        foreach($commandsFound as $commandFound){
                            if(!empty($returnString))
                                $returnString .= ', ';
                            $returnString .= "`{$commandFound}`";
                        }
                        return $returnString;
                    }
                    else
                        $command = $this->discord->commands[$commandsFound[0]];

                }

                $help = $command->getHelp($this->prefix);
                $help['command'] = str_replace(array('!','-'),'',$help['command']);

                $embed = [
                    'author' => [
                        'name' => $this->discord->commandClientOptions['name'],
                        'icon_url' => 'https://cdn.discordapp.com/avatars/730815388400615455/8e1be04d2ff5de27405bd0b36edb5194.png'
                    ],
                    "title" => 'Help: '.$help['command'],
                    "description" => !empty($help['longDescription'])?$help['longDescription']:trans('help.'.$help['command'].'.description', ['prefix' => $this->prefix], $this->lang),
                    'fields' => [],
                    'footer' => array(
                        'text'  => $this->discord->commandClientOptions['name'],
                    ),
                ];

                if(!empty($help['usage']))
                {
                    $embed['fields'][] = array(
                        'name' => 'Usage',
                        'value' => "``".trans('help.'.$help['command'].'.usage', ['prefix' => $this->prefix], $this->lang)."``",
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
                    if(empty($aliasesString))
                        $aliasesString = "/";
                    $embed['fields'][] = array(
                        'name' => 'Aliases',
                        'value' => $aliasesString,
                        'inline' => true
                    );
                }

                $newEmbed = $this->discord->factory(Embed::class,$embed);
                $this->message->channel->sendMessage('', false, $newEmbed);

                return;
            }
        }
        catch(\Exception $e)
        {
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
            return 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
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
                $help['command'] = str_replace(array('!','-'),'',$help['command']);

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
            echo 'File '.basename($e->getFile()).' - Line '.$e->getLine().' -  '.$e->getMessage();
        }
        return $embed;
    }

}
