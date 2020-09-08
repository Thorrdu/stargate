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
            $this->prefix = str_replace((string) $this->discord->user, '@'.$this->discord->username, $this->discord->commandClientOptions['prefix']);

            if(empty($this->args) || $this->args[0] == 'list')
            {
                echo PHP_EOL.'Execute Help';

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
                                echo $e->getMessage();
                            }
                            return true;
                        }
                        else
                            return false;
                    };
                    $this->paginatorMessage->createReactionCollector($filter);
                    return;
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
                $help['command'] = str_replace(array('!','-'),'',$help['command']);

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
            echo $e->getMessage();
        }
        return $embed;
    }

}
