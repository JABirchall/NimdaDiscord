<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nimda\Configuration\Discord;

/**
 * Class CommandContainer
 * @package Nimda\Core
 */
final class CommandContainer
{
    const CORE_COMMAND = 'Nimda\\Core\\Commands\\';
    const CORE_COMMAND_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_COMMAND = 'Nimda\\Commands\\';
    const PUBLIC_COMMAND_CONFIG = 'Nimda\\Commands\\Configuration\\';

    /**
     * @var \Illuminate\Support\Collection $commands
     */
    protected $commands;

    /**
     * commandContainer constructor.
     */
    public function __construct()
    {
        $this->commands = new Collection();
    }

    /**
     * Setup commands to receive events
     */
    public function loadCommands()
    {
        $this->loadCoreCommands(Discord::$config['commands']['core']);
        $this->loadPublicCommands(Discord::$config['commands']['public']);

        printf("Loading commands completed\n");
    }

    /**
     * Setup core command to receive events
     *
     * @param array $commands
     */
    private function loadCoreCommands(array $commands)
    {
        foreach ($commands as $command) {
            if(!$this->precheckCommand(self::CORE_COMMAND, $command)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_COMMAND, $command);

            if($config === null) {
                continue;
            }

            $loadedCommand = new $command($config);
            $this->setTrigger($loadedCommand, $config);

            printf("Completed\n");
        }
    }

    /**
     * Setup public commands to receive events
     *
     * @param array $commands
     */
    private function loadPublicCommands(array $commands)
    {
        foreach ($commands as $command) {
            if(!$this->precheckCommand(self::PUBLIC_COMMAND, $command)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_COMMAND, $command);

            if($config === null) {
                continue;
            }

            $loadedCommand = new $command($config);
            $this->setTrigger($loadedCommand, $config);

            printf("Completed\n");
        }
    }

    /**
     * Validate a command is correctly setup before loading
     *
     * @param $namespace
     * @param $command
     *
     * @return bool
     */
    private function precheckCommand($namespace, $command)
    {
        $commandClass = substr($command, strlen($namespace));

        $type = ($namespace == self::CORE_COMMAND) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} command [{$commandClass}]", ":: ");

        if (!class_exists($command)) {
            printf("Loading failed because class %s doesn't exist.\n", $commandClass);
            return false;
        }

        if(!is_subclass_of($command, Command::class))
        {
            printf("Loading failed because class %s doesn't extend %s.\n", $commandClass, Command::class);
            return false;
        }

        return true;
    }

    /**
     * @internal Add the a command command mapped to its corresponding command to the container
     *
     * @param Command $commandClass
     * @param array $config
     */
    private function setTrigger(Command $commandClass, array $config)
    {
        if(array_key_exists('commands', $config['trigger'])) {
            $commands = $config['trigger']['commands'];
            foreach ($commands as $command) {
                $this->commands->push([$command => $commandClass]);
            }
        }
    }

    /**
     * @internal Load a ccommands configuration
     *
     * @param $namespace
     * @param $command
     *
     * @return array|null
     */
    private function loadConfig($namespace, $command)
    {
        $command = substr($command, strlen($namespace));
        $class = ($namespace == self::CORE_COMMAND) ?
            self::CORE_COMMAND_CONFIG . $command :
            self::PUBLIC_COMMAND_CONFIG . $command;

        if (!class_exists($class)) {
            printf("Loading failed because command %s does not have a config\n", $command);
            return null;
        }

        return $class::$config;
    }

    /**
     * Check a message for chat command
     *
     * @param Message $message
     */
    public function onMessage(Message $message)
    {
        if(!Str::startsWith($message->content, Discord::$config['prefix']) || $message->author->bot) {
            return;
        }

        $plainText = Str::lower(Str::after($message->content, Discord::$config['prefix']));

        $command = $this->findCommand($plainText);

        if($command->isEmpty()) {
            return;
        }

        $arguments = $this->parseArguments($plainText, $command->keys()->first());

        if($arguments === false) {
            return;
        }

        $command = $command->first();
        $command->trigger($message, $arguments);
    }

    /**
     * @internal Find a command for a chat command
     *
     * @param $text
     *
     * @return Collection
     */
    private function findCommand($text)
    {
        return $this->commands->filter(function ($command) use ($text){
            $commandShard = \explode(' ', \array_keys($command)[0])[0];
            return Str::startsWith($text, $commandShard);
        })->collapse();
    }

    /**
     * @internal Checks and parses a chat command arguments
     *
     * @param string $message
     * @param string $pattern
     *
     * @return array|false
     */
    private function parseArguments($message, $pattern)
    {
        //$commandRegex = '/\{((?:(?!\d+,?\d+?)\w)+?)\}/'; // Saved for legacy
        $commandRegex = '/\{((?:(?!\d)\w)+?):?(?:(?<=\:)([[:graph:]]+))?\}/';
        $pattern = str_replace('/', '\/', $pattern);
        //$regex = '/^'.\preg_replace($commandRegex, '(?<$1>.*)', $pattern).' ?/miu'; // Saved for legacy

        $onMatch = function ($matches) {
            $pattern = $matches[2]??".*";
            return "(?<{$matches[1]}>{$pattern})";
        };

        $regex = '/^'.\preg_replace_callback($commandRegex, $onMatch, $pattern).' ?/miu';
        $regexMatched = (bool)\preg_match($regex, $message, $matches);

        if($regexMatched === true)
        {
            return $matches;
        }

        return false;
    }
}
