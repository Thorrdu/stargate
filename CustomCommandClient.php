<?php

/*
 * This file is apart of the DiscordPHP project.
 *
 * Copyright (c) 2016-2020 David Cole <david.cole1340@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the LICENSE.md file.
 */

namespace Discord;

use App\Channel;
use App\Guild;
use Discord\CustomCommand;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Provides an easy way to have triggerable commands.
 */
class myDiscordCommandClient extends Discord
{
    /**
     * An array of options passed to the client.
     *
     * @var array Options.
     */
    public $commandClientOptions;

    /**
     * A map of the commands.
     *
     * @var array CustomCommands.
     */
    public $commands = [];

    /**
     * A map of aliases for commands.
     *
     * @var array Aliases.
     */
    public $aliases = [];

    /**
     * Constructs a new command client.
     *
     * @param  array      $options An array of options.
     * @throws \Exception
     */
    public function __construct(array $options = [])
    {
        $this->commandClientOptions = $this->resolveCommandClientOptions($options);

        $discordOptions = array_merge($this->commandClientOptions['discordOptions'], [
            'token' => $this->commandClientOptions['token'],
        ]);

        parent::__construct($discordOptions);

        $this->on('ready', function () {
            $this->commandClientOptions['prefix'] = str_replace('@mention', (string) $this->user, $this->commandClientOptions['prefix']);
            $this->commandClientOptions['name'] = str_replace('<UsernamePlaceholder>', $this->username, $this->commandClientOptions['name']);

            $guilds = Guild::all();
            $guildConfig = [];
            foreach($guilds as $guild)
            {
                $guildConfig[$guild->guild_id] = ['prefix' => $guild->prefix];
            }
            Config::set('stargate.guilds', $guildConfig);
            echo PHP_EOL.'GUILD LOADED';

            $channels = Channel::all();
            $channelsConfig = [];
            foreach($channels as $channel)
            {
                $channelsConfig[$channel->channel_id] = ['ignore' => ($channel->ignore)?'on':'off'];
            }
            Config::set('stargate.channels', $channelsConfig);
            echo PHP_EOL.'CHANNELS LOADED';

            $this->on('message', function ($message) {
                if ($message->author->id == $this->id || (isset($message->author->bot) && $message->author->bot == true) || (isset($message->author->user->bot) && $message->author->user->bot == true)) {
                    return;
                }

                $dmChannel = false;
                $prefix = $this->commandClientOptions['prefix'];
                if(!is_null($message->channel->guild_id))
                {
                    $guildConfig = config('stargate.guilds.'.$message->channel->guild_id);
                    if(!is_null($guildConfig))
                        $prefix = $guildConfig['prefix'];
                }
                /*else
                    $dmChannel = true;*/

                $channelIgnore = config('stargate.channels.'.$message->channel->id.'.ignore');
                if(!is_null($channelIgnore) && $channelIgnore == 'on' && !Str::startsWith($message->content, $prefix.'channel'))
                    return;

                $withoutPrefix = '';
                if (substr($message->content, 0, strlen($prefix)) == $prefix || $dmChannel){
                    if($dmChannel && !(substr($message->content, 0, strlen($prefix)) == $prefix))
                        $withoutPrefix = $message->content;
                    else
                        $withoutPrefix = substr($message->content, strlen($prefix));

                    if(!empty($withoutPrefix))
                    {
                        $args = str_getcsv($withoutPrefix, ' ');
                        $command = strtolower(array_shift($args));
    
                        $commandsFound = [];
                        $commandKeys = array_keys($this->commands);

                        foreach($commandKeys as $key)
                        {
                            $len = strlen($command);
                            if((substr($key, 0, $len) === $command)) 
                            {
                                if($key != 'ban' || $message->author->id == 125641223544373248)
                                    $commandsFound[] = $key;
                            }
                        }

                        if(array_key_exists($command, $this->aliases)) {
                            $command = $this->commands[$this->aliases[$command]];
                        }elseif(empty($commandsFound))
                        {
                            return;
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
                            $message->reply($returnString);
                            return ;
                        }
                        else
                            $command = $this->commands[$commandsFound[0]];

                        /*
                        if (array_key_exists($command, $this->commands)) {
                            $command = $this->commands[$command];
                        } elseif (array_key_exists($command, $this->aliases)) {
                            $command = $this->commands[$this->aliases[$command]];
                        } else {
                            // CustomCommand doesn't exist.
                            return;
                        }*/
    
                        $result = $command->handle($message, $args);
    
                        if (is_string($result)) {
                            $message->reply($result);
                        }
                    }
                }
            });
        });

        if ($this->commandClientOptions['defaultHelpCommand']) {
            $this->registerCommand('help', function ($message, $args) {
                $prefix = str_replace((string) $this->user, '@'.$this->username, $this->commandClientOptions['prefix']);

                if (count($args) > 0) {
                    $commandString = implode(' ', $args);
                    $command = $this->getCommand($commandString);

                    if (is_null($command)) {
                        return "The command {$commandString} does not exist.";
                    }

                    $help = $command->getHelp($prefix);

                    /**
                     * @todo Use internal Embed::class
                     */
                    $embed = [
                        'author' => [
                            'name' => $this->commandClientOptions['name'],
                            'icon_url' => $this->client->user->avatar,
                        ],
                        'title' => $help['command'].'\'s Help',
                        'description' => ! empty($help['longDescription']) ? $help['longDescription'] : $help['description'],
                        'fields' => [],
                        'footer' => [
                            'text' => $this->commandClientOptions['name'],
                        ],
                    ];

                    if (! empty($help['usage'])) {
                        $embed['fields'][] = [
                            'name' => 'Usage',
                            'value' => '``'.$help['usage'].'``',
                            'inline' => true,
                        ];
                    }

                    if (! empty($this->aliases)) {
                        $aliasesString = '';
                        foreach ($this->aliases as $alias => $command) {
                            if ($command != $commandString) {
                                continue;
                            }

                            $aliasesString .= "{$alias}\r\n";
                        }
                        $embed['fields'][] = [
                            'name' => 'Aliases',
                            'value' => $aliasesString,
                            'inline' => true,
                        ];
                    }

                    /*
                    if (! empty($help['subCommandsHelp'])) {
                        foreach ($help['subCommandsHelp'] as $subCommandHelp) {
                            $embed['fields'][] = [
                                'name' => $subCommandHelp['command'],
                                'value' => $subCommandHelp['description'],
                                'inline' => true,
                            ];
                        }
                    }*/

                    $message->channel->sendMessage('', false, $embed);

                    return;
                }

                /**
                 * @todo Use internal Embed::class
                 */
                $embed = [
                    'author' => [
                        'name' => $this->commandClientOptions['name'],
                        'icon_url' => $this->client->avatar,
                    ],
                    'title' => $this->commandClientOptions['name'].'\'s Help',
                    'description' => $this->commandClientOptions['description']."\n\nRun `{$prefix}help` command to get more information about a specific command.\n----------------------------",
                    'fields' => [],
                    'footer' => [
                        'text' => $this->commandClientOptions['name'],
                    ],
                ];

                // Fallback in case commands count reaches the fields limit
                if (count($this->commands) > 20) {
                    foreach ($this->commands as $command) {
                        $help = $command->getHelp($prefix);
                        $embed['description'] .= "\n\n`".$help['command']."`\n".$help['description'];
                    }
                } else {
                    foreach ($this->commands as $command) {
                        $help = $command->getHelp($prefix);
                        $embed['fields'][] = [
                            'name' => $help['command'],
                            'value' => $help['description'],
                            'inline' => true,
                        ];
                    }
                }

                $message->channel->sendMessage('', false, $embed);
            }, [
                'description' => 'Provides a list of commands available.',
                'usage' => '[command]',
            ]);
        }
    }

    /**
     * Registers a new command.
     *
     * @param string           $command  The command name.
     * @param \Callable|string $callable The function called when the command is executed.
     * @param array            $options  An array of options.
     *
     * @return CustomCommand    The command instance.
     * @throws \Exception
     */
    public function registerCommand(string $command, $callable, array $options = []): CustomCommand
    {
        if (array_key_exists($command, $this->commands)) {
            throw new \Exception("A command with the name {$command} already exists.");
        }

        list($commandInstance, $options) = $this->buildCommand($command, $callable, $options);
        $this->commands[$command] = $commandInstance;

        foreach ($options['aliases'] as $alias) {
            $this->registerAlias($alias, $command);
        }

        return $commandInstance;
    }

    /**
     * Unregisters a command.
     *
     * @param  string     $command The command name.
     * @throws \Exception
     */
    public function unregisterCommand(string $command): void
    {
        if (! array_key_exists($command, $this->commands)) {
            throw new \Exception("A command with the name {$command} does not exist.");
        }

        unset($this->commands[$command]);
    }

    /**
     * Registers a command alias.
     *
     * @param string $alias   The alias to add.
     * @param string $command The command.
     */
    public function registerAlias(string $alias, string $command): void
    {
        $this->aliases[$alias] = $command;
    }

    /**
     * Unregisters a command alias.
     *
     * @param  string     $alias The alias name.
     * @throws \Exception
     */
    public function unregisterCommandAlias(string $alias): void
    {
        if (! array_key_exists($alias, $this->aliases)) {
            throw new \Exception("A command alias with the name {$alias} does not exist.");
        }

        unset($this->aliases[$alias]);
    }

    /**
     * Attempts to get a command.
     *
     * @param string $command The command to get.
     * @param bool   $aliases Whether to search aliases as well.
     *
     * @return CustomCommand|null The command.
     */
    public function getCommand(string $command, bool $aliases = true): ?CustomCommand
    {
        if (array_key_exists($command, $this->commands)) {
            return $this->commands[$command];
        }

        if (array_key_exists($command, $this->aliases) && $aliases) {
            return $this->commands[$this->aliases[$command]];
        }

        return null;
    }

    /**
     * Builds a command and returns it.
     *
     * @param string           $command  The command name.
     * @param \Callable|string $callable The function called when the command is executed.
     * @param array            $options  An array of options.
     *
     * @return array[CustomCommand, array] The command instance and options.
     * @throws \Exception
     */
    public function buildCommand(string $command, $callable, array $options = []): array
    {
        if (is_string($callable)) {
            $callable = function ($message) use ($callable) {
                return $callable;
            };
        } elseif (is_array($callable) && ! is_callable($callable)) {
            $callable = function ($message) use ($callable) {
                return $callable[array_rand($callable)];
            };
        }

        if (! is_callable($callable)) {
            throw new \Exception('The callable parameter must be a string, array or callable.');
        }

        $options = $this->resolveCommandOptions($options);

        $commandInstance = new CustomCommand(
            $this, $command, $callable,
            $options['group'], $options['description'], $options['longDescription'], $options['usage'], $options['cooldown'], $options['cooldownMessage']);

        return [$commandInstance, $options];
    }

    /**
     * Resolves command options.
     *
     * @param array $options Array of options.
     *
     * @return array Options.
     */
    protected function resolveCommandOptions(array $options): array
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setDefined([
                'group',
                'description',
                'longDescription',
                'usage',
                'aliases',
                'cooldown',
                'cooldownMessage',
            ])
            ->setDefaults([
                'group' => 'game',
                'description' => 'No description provided.',
                'longDescription' => '',
                'usage' => '',
                'aliases' => [],
                'cooldown' => 0,
                'cooldownMessage' => 'please wait %d second(s) to use this command again.',
            ]);

        $options = $resolver->resolve($options);

        if (! empty($options['usage'])) {
            $options['usage'] .= ' ';
        }

        return $options;
    }

    /**
     * Resolves the options.
     *
     * @param array $options Array of options.
     *
     * @return array Options.
     */
    protected function resolveCommandClientOptions(array $options): array
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setRequired('token')
            ->setAllowedTypes('token', 'string')
            ->setDefined([
                'token',
                'prefix',
                'name',
                'shardId',
                'shardCount',
                'description',
                'defaultHelpCommand',
                'discordOptions',
            ])
            ->setDefaults([
                'prefix' => '@mention ',
                'name' => '<UsernamePlaceholder>',
                'description' => 'A bot made with DiscordPHP ' . self::VERSION . '.',
                'defaultHelpCommand' => true,
                'discordOptions' => [],
            ]);

        return $resolver->resolve($options);
    }

    /**
     * Handles dynamic get calls to the command client.
     *
     * @param string $name Variable name.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $allowed = ['commands', 'aliases'];

        if (array_search($name, $allowed) !== false) {
            return $this->{$name};
        }

        return parent::__get($name);
    }
}
