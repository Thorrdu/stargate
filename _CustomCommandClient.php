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

use Discord\CustomCommand;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Guild;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;



/**
 * Provides an easy way to have triggerable commands.
 */
class CustomCommandClient extends Discord
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
     * @var array Commands.
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
     * @param array $options An array of options.
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


            $this->on('message', function ($message) {
                if ($message->author->id == $this->id || (isset($message->author->bot) && $message->author->bot == true) || (isset($message->author->user->bot) && $message->author->user->bot == true)) {
                    return;
                }

                $prefix = $this->commandClientOptions['prefix'];
                if(!is_null($message->channel->guild_id))
                {
                    $guildConfig = config('stargate.guilds.'.$message->channel->guild_id);
                    if(!is_null($guildConfig))
                        $prefix = $guildConfig['prefix'];
                }


                $withoutPrefix = '';
                if (substr($message->content, 0, strlen($prefix)) == $prefix)
                    $withoutPrefix = substr($message->content, strlen($prefix));                    
                    /*
                elseif(substr($message->content, 0, strlen($this->username)) == $this->username)
                    $withoutPrefix = substr($message->content, strlen($this->username));
                elseif(substr($message->content, 0, strlen('<@'.$this->user->id.'> ')) == '<@'.$this->user->id.'> ')
                    $withoutPrefix = substr($message->content, strlen('<@'.$this->user->id.'> '));
*/
                //echo PHP_EOL.'wo'.$withoutPrefix;
                if(!empty($withoutPrefix))
                {
                    $args = str_getcsv($withoutPrefix, ' ');
                    $command = array_shift($args);

                    //var_dump($this->commands);

                    if (array_key_exists($command, $this->commands)) {
                        $command = $this->commands[$command];
                    } elseif (array_key_exists($command, $this->aliases)) {
                        $command = $this->commands[$this->aliases[$command]];
                    } else {
                        // Command doesn't exist.
                        return;
                    }

                    $result = $command->handle($message, $args);

                    if (is_string($result)) {
                        $message->reply($result);
                    }
                }
            });
        });

    }

    /**
     * Registers a new command.
     *
     * @param string           $command  The command name.
     * @param \Callable|string $callable The function called when the command is executed.
     * @param array            $options  An array of options.
     *
     * @return CustomCommand The command instance.
     */
    public function registerCommand($command, $callable, array $options = [])
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
     * @param string $command The command name.
     */
    public function unregisterCommand($command)
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
    public function registerAlias($alias, $command)
    {
        $this->aliases[$alias] = $command;
    }

    /**
     * Unregisters a command alias.
     *
     * @param string $alias The alias name.
     */
    public function unregisterCommandAlias($alias)
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
    public function getCommand($command, $aliases = true)
    {
        if (array_key_exists($command, $this->commands)) {
            return $this->commands[$command];
        }

        if (array_key_exists($command, $this->aliases) && $aliases) {
            return $this->commands[$this->aliases[$command]];
        }
    }

    /**
     * Builds a command and returns it.
     *
     * @param string           $command  The command name.
     * @param \Callable|string $callable The function called when the command is executed.
     * @param array            $options  An array of options.
     *
     * @return array[CustomCommand, array] The command instance and options.
     */
    public function buildCommand($command, $callable, array $options = [])
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
            $options['description'], $options['longDescription'], $options['usage'], $options['cooldown'], $options['cooldownMessage']);

        return [$commandInstance, $options];
    }

    /**
     * Resolves command options.
     *
     * @param array $options Array of options.
     *
     * @return array Options.
     */
    protected function resolveCommandOptions(array $options)
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setDefined([
                'description',
                'longDescription',
                'usage',
                'aliases',
                'cooldown',
                'cooldownMessage',
            ])
            ->setDefaults([
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
    protected function resolveCommandClientOptions(array $options)
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
                'description' => 'A bot made with DiscordPHP.',
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
    public function __get($name)
    {
        $allowed = ['commands', 'aliases'];

        if (array_search($name, $allowed) !== false) {
            return $this->{$name};
        }

        return parent::__get($name);
    }
}
