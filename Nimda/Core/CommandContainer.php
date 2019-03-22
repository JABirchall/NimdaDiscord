<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nimda\Configuration\Discord;

/**
 * Class PluginContainer
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
     * PluginContainer constructor.
     */
    public function __construct()
    {
        $this->commands = new Collection();
    }

    /**
     * Setup plugins to receive events
     */
    public function loadCommands()
    {
        $this->loadCoreCommands(Discord::$config['commands']['core']);
        $this->loadPublicCommands(Discord::$config['commands']['public']);

        printf("Loading plugins completed\n");
    }

    /**
     * Setup core plugins to receive events
     *
     * @param array $plugins
     */
    private function loadCoreCommands(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if(!$this->precheckPlugin(self::CORE_COMMAND, $plugin)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_COMMAND, $plugin);

            if($config === null) {
                continue;
            }

            $loadedPlugin = new $plugin($config);
            $this->setTrigger($loadedPlugin, $config);

            printf("Completed\n");
        }
    }

    /**
     * Setup public plugins to receive events
     *
     * @param array $plugins
     */
    private function loadPublicCommands(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if(!$this->precheckPlugin(self::PUBLIC_COMMAND, $plugin)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_COMMAND, $plugin);

            if($config === null) {
                continue;
            }

            $loadedPlugin = new $plugin($config);
            $this->setTrigger($loadedPlugin, $config);

            printf("Completed\n");
        }
    }

    /**
     * Validate a plugin is correctly setup before loading
     *
     * @param $namespace
     * @param $plugin
     *
     * @return bool
     */
    private function precheckPlugin($namespace, $plugin)
    {
        $pluginName = substr($plugin, strlen($namespace));

        $type = ($namespace == self::CORE_COMMAND) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} plugin [{$pluginName}]", ":: ");

        if (!class_exists($plugin)) {
            printf("Loading failed because class %s doesn't exist.\n", $pluginName);
            return false;
        }

        if(!is_subclass_of($plugin, Command::class))
        {
            printf("Loading failed because class %s doesn't extend %s.\n", $pluginName, Command::class);
            return false;
        }

        return true;
    }

    /**
     * @internal Add the a plugin command mapped to its corresponding plugin to the container
     *
     * @param Command $plugin
     * @param array $config
     */
    private function setTrigger(Command $plugin, array $config)
    {
        if(array_key_exists('commands', $config['trigger'])) {
            $commands = $config['trigger']['commands'];
            foreach ($commands as $command) {
                $this->commands->push([$command => $plugin]);
            }
        }
    }

    /**
     * @internal Load a plugins configuration
     *
     * @param $namespace
     * @param $plugin
     *
     * @return array|null
     */
    private function loadConfig($namespace, $plugin)
    {
        $plugin = substr($plugin, strlen($namespace));
        $class = ($namespace == self::CORE_COMMAND) ?
            self::CORE_COMMAND_CONFIG . $plugin :
            self::PUBLIC_COMMAND_CONFIG . $plugin;

        if (!class_exists($class)) {
            printf("Loading failed because plugin %s does not have a config\n", $plugin);
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
     * @internal Find a plugin for a chat command
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
