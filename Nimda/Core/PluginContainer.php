<?php

namespace Nimda\Core;


use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PluginContainer
{
    const CORE_PLUGIN = 'Nimda\\Core\\Plugins\\';
    const CORE_PLUGIN_CONFIG = 'Nimda\\Configuration\\Core\\';
    const PUBLIC_PLUGIN = 'Nimda\Plugins';

    protected $collection;
    protected $commands;

    public function __construct()
    {
        $this->collection = new Collection();
        $this->commands =  new Collection();
    }

    public function loadPlugins(Client $client, $plugins)
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
            $this->setTrigger($client, $loadedPlugin, $config);
            $this->collection->push($loadedPlugin);
            printf("Complete.\n");
        }

    }

    private function loadConfig($plugin)
    {
        $class = \Nimda\Configuration\Core::class . '\\' . $plugin;
        return $class::$config;
    }

    private function setTrigger(Client $client, $plugin, $config)
    {
        if(array_key_exists('timeout', $config['trigger'])) {
            $client->addTimer($config['trigger']['timeout'], [$plugin, 'trigger']);
        }

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

        $plugin = $this->commands->filter(function ($plugin) use ($message){
            $command = array_keys($plugin)[0];
            $res = Str::startsWith($message->content, $command);
            return $res;
        });

        if($plugin->isEmpty()) {
            return;
        }

        $plugin->collapse()->first()->trigger($message);
    }
}