<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PluginContainer
{
    const CORE_PLUGIN = 'Nimda\\Core\\Plugins\\';
    const CORE_PLUGIN_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_PLUGIN = 'Nimda\Plugins';

    protected $commands;

    public function __construct()
    {
        $this->commands =  new Collection();
    }

    public function loadPlugins($plugins)
    {
        foreach ($plugins as $plugin) {
            $pluginName = substr($plugin, strlen(self::CORE_PLUGIN));
            printf("%- 50s %s", "Loading core plugin [{$pluginName}]", ":: ");

            if (!class_exists($plugin)) {
                printf("Loading failed because class %s doesn't exist.\n", $pluginName);
                continue;
            }

            if (!class_exists(self::CORE_PLUGIN_CONFIG.$pluginName)) {
                printf("Loading failed because class %s does not have a config\n", $pluginName);
                continue;
            }

            $config = $this->loadConfig($pluginName);

            if($config === null) {
                printf("Plugin %s with config file has not been loaded because it doesn't exist.\n", $pluginName);
                continue;
            }

            $loadedPlugin = new $plugin($config);
            $this->setTrigger($loadedPlugin, $config);

            printf("Complete.\n");
        }

    }

    private function setTrigger($plugin, $config)
    {
        if(array_key_exists('commands', $config['trigger'])) {
            foreach ($config['trigger']['commands'] as $command) {
                $this->commands->push([$command => $plugin]);
            }
        }
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

    private function loadConfig($plugin)
    {
        $class = self::CORE_PLUGIN_CONFIG . $plugin;
        return $class::$config;
    }

}