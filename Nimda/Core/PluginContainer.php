<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PluginContainer
{
    const CORE_PLUGIN = 'Nimda\\Core\\Plugins\\';
    const CORE_PLUGIN_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_PLUGIN = 'Nimda\\Plugins\\';
    const PUBLIC_PLUGIN_CONFIG = 'Nimda\\Plugins\\Configuration\\';

    protected $commands;

    public function __construct()
    {
        $this->commands =  new Collection();
    }

    public function loadPlugins($plugins)
    {
        $this->loadCorePlugins($plugins['core']);
        $this->loadPublicPlugins($plugins['public']);

        printf("Complete.\n");
    }

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
        }
    }

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
        }
    }

    private function precheckPlugin($namespace, $plugin)
    {
        $pluginName = substr($plugin, strlen($namespace));

        printf("%- 50s %s", "Loading core plugin [{$pluginName}]", ":: ");

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

    private function setTrigger($plugin, $config)
    {
        if(array_key_exists('commands', $config['trigger'])) {
            foreach ($config['trigger']['commands'] as $command) {
                $this->commands->push([$command => $plugin]);
            }
        }
    }

    private function loadConfig($namespace, $plugin)
    {
        $plugin = substr($plugin, strlen($namespace));
        $class = self::CORE_PLUGIN_CONFIG . $plugin;

        if (!class_exists($class)) {
            printf("Loading failed because plugin %s does not have a config\n", $plugin);
            return null;
        }

        return $class::$config;
    }

    public function onMessage(Message $message)
    {
        if($message->author->bot) {
            return;
        }

        $plugin = $this->findPluginByCommand($message);

        if($plugin === null) {
            return;
        }

        $plugin->trigger($message);
    }


    private function findPluginByCommand($message)
    {
        return $this->commands->filter(function ($plugin) use ($message){
            $command = array_keys($plugin);
            return Str::startsWith($message->content, $command[0]);
        })->collapse()
            ->first();
    }
}
