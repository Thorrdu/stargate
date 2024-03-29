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

use Discord\myDiscordCommandClient;
use Discord\Parts\Channel\Message;

/**
 * A command that the Command Client will listen for.
 */
class CustomCommand
{
    /**
     * The trigger for the command.
     *
     * @var string Command trigger.
     */
    protected $command;

    /**
     * The Group of the command.
     *
     * @var string Group.
     */
    public $group;

    /**
     * The short description of the command.
     *
     * @var string Description.
     */
    protected $description;

    /**
     * The long description of the command.
     *
     * @var string Long description.
     */
    protected $longDescription;

    /**
     * The usage of the command.
     *
     * @var string Command usage.
     */
    protected $usage;

    /**
     * The cooldown of the command in milliseconds.
     *
     * @var int Command cooldown.
     */
    protected $cooldown;

    /**
     * The cooldown message to show when a cooldown is in effect.
     *
     * @var string Command cooldown message.
     */
    protected $cooldownMessage;

    /**
     * An array of cooldowns for commands.
     *
     * @var array Cooldowns.
     */
    protected $cooldowns = [];

    /**
     * A map of sub-commands.
     *
     * @var array Sub-Commands.
     */
    protected $subCommands = [];

    /**
     * A map of sub-command aliases.
     *
     * @var array Sub-Command aliases.
     */
    protected $subCommandAliases = [];

    /**
     * Creates a command instance.
     *
     * @param myDiscordCommandClient $client          The Discord Command Client.
     * @param string               $command         The command trigger.
     * @param \Callable            $callable        The callable function.
     * @param string               $group           The command group.
     * @param string               $description     The short description of the command.
     * @param string               $longDescription The long description of the command.
     * @param string               $usage           The usage of the command.
     * @param int                  $cooldown        The cooldown of the command in milliseconds.
     * @param int                  $cooldownMessage The cooldown message to show when a cooldown is in effect.
     */
    public function __construct(
        myDiscordCommandClient $client,
        $command,
        callable $callable,
        $group,
        $description,
        $longDescription,
        $usage,
        $cooldown,
        $cooldownMessage
    ) {
        $this->client = $client;
        $this->command = $command;
        $this->callable = $callable;
        $this->group = $group;
        $this->description = $description;
        $this->longDescription = $longDescription;
        $this->usage = $usage; 
        $this->cooldown = $cooldown;
        $this->cooldownMessage = $cooldownMessage;
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
    public function registerSubCommand($command, $callable, array $options = [])
    {
        if (array_key_exists($command, $this->subCommands)) {
            throw new \Exception("A sub-command with the name {$command} already exists.");
        }

        list($commandInstance, $options) = $this->client->buildCommand($command, $callable, $options);
        $this->subCommands[$command] = $commandInstance;

        foreach ($options['aliases'] as $alias) {
            $this->registerSubCommandAlias($alias, $command);
        }

        return $commandInstance;
    }

    /**
     * Unregisters a sub-command.
     *
     * @param string $command The command name.
     */
    public function unregisterSubCommand($command)
    {
        if (! array_key_exists($command, $this->subCommands)) {
            throw new \Exception("A sub-command with the name {$command} does not exist.");
        }

        unset($this->subCommands[$command]);
    }

    /**
     * Registers a sub-command alias.
     *
     * @param string $alias   The alias to add.
     * @param string $command The command.
     */
    public function registerSubCommandAlias($alias, $command)
    {
        $this->subCommandAliases[$alias] = $command;
    }

    /**
     * Unregisters a sub-command alias.
     *
     * @param string $alias The alias name.
     */
    public function unregisterSubCommandAlias($alias)
    {
        if (! array_key_exists($alias, $this->subCommandAliases)) {
            throw new \Exception("A sub-command alias with the name {$alias} does not exist.");
        }

        unset($this->subCommandAliases[$alias]);
    }

    /**
     * Executes the command.
     *
     * @param Message $message The message.
     * @param array   $args    An array of arguments.
     *
     * @return mixed The response.
     */
    public function handle(Message $message, array $args)
    {
        $subCommand = array_shift($args);

        if (array_key_exists($subCommand, $this->subCommands)) {
            return $this->subCommands[$subCommand]->handle($message, $args);
        } elseif (array_key_exists($subCommand, $this->subCommandAliases)) {
            return $this->subCommands[$this->subCommandAliases[$subCommand]]->handle($message, $args);
        }

        if (! is_null($subCommand)) {
            array_unshift($args, $subCommand);
        }

        $currentTime = round(microtime(true) * 1000);
        if (isset($this->cooldowns[$message->author->id])) {
            if ($this->cooldowns[$message->author->id] < $currentTime) {
                $this->cooldowns[$message->author->id] = $currentTime + $this->cooldown*1000;
            } else {
                return sprintf($this->cooldownMessage, (($this->cooldowns[$message->author->id] - $currentTime) / 1000));
            }
        } else {
            $this->cooldowns[$message->author->id] = $currentTime + $this->cooldown;
        }

        return call_user_func_array($this->callable, [$message, $args]);
    }

    /**
     * Gets help for the command.
     *
     * @param string $prefix The prefix of the bot.
     *
     * @return string The help.
     */
    public function getHelp($prefix)
    {
        $subCommandsHelp = [];

        foreach ($this->subCommands as $command) {
            $subCommandsHelp[] = $command->getHelp($prefix.$this->command.' ');
        }

        return [
            'command' => $prefix.$this->command,
            'group' => $this->group,
            'description' => $this->description,
            'longDescription' => $this->longDescription,
            'usage' => $this->usage,
            'cooldown' => $this->cooldown,
            'subCommandsHelp' => $subCommandsHelp,
        ];
    }

    /**
     * Handles dynamic get calls to the class.
     *
     * @param string $variable The variable to get.
     *
     * @return mixed The value.
     */
    public function __get($variable)
    {
        $allowed = ['command', 'group', 'description', 'longDescription', 'usage', 'cooldown', 'cooldownMessage'];

        if (array_search($variable, $allowed) !== false) {
            return $this->{$variable};
        }
    }
}
