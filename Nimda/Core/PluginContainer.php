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
final class PluginContainer
{
    const CORE_PLUGIN = 'Nimda\\Core\\Plugins\\';
    const CORE_PLUGIN_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_PLUGIN = 'Nimda\\Plugins\\';
    const PUBLIC_PLUGIN_CONFIG = 'Nimda\\Plugins\\Configuration\\';

    /**
     * @var \Illuminate\Support\Collection
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
    public function loadPlugins()
    {
        $this->loadCorePlugins(Discord::$config['plugins']['core']);
        $this->loadPublicPlugins(Discord::$config['plugins']['public']);

        printf("Loading plugins completed\n");
    }

    /**
     * Setup core plugins to receive events
     * @param array $plugins
     */
    private function loadCorePlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if(!$this->precheckPlugin(self::CORE_PLUGIN, $plugin)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_PLUGIN, $plugin);

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
     * @param array $plugins
     */
    private function loadPublicPlugins(array $plugins)
    {
        foreach ($plugins as $plugin) {
            if(!$this->precheckPlugin(self::PUBLIC_PLUGIN, $plugin)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_PLUGIN, $plugin);

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
     * @param $namespace
     * @param $plugin
     * @return bool
     */
    private function precheckPlugin($namespace, $plugin)
    {
        $pluginName = substr($plugin, strlen($namespace));

        $type = ($namespace == self::CORE_PLUGIN) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} plugin [{$pluginName}]", ":: ");

        if (!class_exists($plugin)) {
            printf("Loading failed because class %s doesn't exist.\n", $pluginName);
            return false;
        }

        if(!is_subclass_of($plugin, Plugin::class))
        {
            printf("Loading failed because class %s doesn't extend %s.\n", $pluginName, Plugin::class);
            return false;
        }

        return true;
    }

    /**
     * Add the a plugin command mapped to its corresponding plugin to the container
     * @param Plugin $plugin
     * @param array $config
     */
    private function setTrigger(Plugin $plugin, array $config)
    {
        if(array_key_exists('commands', $config['trigger'])) {
            $commands = $config['trigger']['commands'];
            foreach ($commands as $command) {
                $this->commands->push([$command => $plugin]);
            }
        }
    }

    /**
     * Load a plugins configuration
     * @param $namespace
     * @param $plugin
     * @return array|null
     */
    private function loadConfig($namespace, $plugin)
    {
        $plugin = substr($plugin, strlen($namespace));
        $class = ($namespace == self::CORE_PLUGIN) ?
            self::CORE_PLUGIN_CONFIG . $plugin :
            self::PUBLIC_PLUGIN_CONFIG . $plugin;

        if (!class_exists($class)) {
            printf("Loading failed because plugin %s does not have a config\n", $plugin);
            return null;
        }

        return $class::$config;
    }

    /**
     * Check a message for chat command
     * @param Message $message
     */
    public function onMessage(Message $message)
    {
        if(!Str::startsWith($message->content, Discord::$config['prefix']) || $message->author->bot) {
            return;
        }

        $plainText = Str::lower(Str::after($message->content, Discord::$config['prefix']));

        $plugin = $this->findPluginByCommand($plainText);

        if($plugin->isEmpty()) {
            return;
        }

        $arguments = $this->parseArguments($plainText, $plugin->keys()->first());

        if($arguments === false) {
            return;
        }

        $plugin = $plugin->first();
        $plugin->trigger($message, $arguments);
    }

    /**
     * Find a plugin for a chat command
     * @param $text
     * @return Collection
     */
    private function findPluginByCommand($text)
    {
        return $this->commands->filter(function ($plugin) use ($text){
            $commandShard = \explode(' ', \array_keys($plugin)[0])[0];
            return Str::startsWith($text, $commandShard);
        })->collapse();
    }

    /**
     * Checks and parses a chat command arguments
     * @param string $message
     * @param string $pattern
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
            return "(?<{$matches[1]}>".$pattern.")";
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
