<?php declare(strict_types=1);

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nimda\Configuration\Discord;
use React\Promise\PromiseInterface;

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
    public function loadCommands(): void
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
    private function loadCoreCommands(array $commands): void
    {
        foreach ($commands as $command) {
            if (!$this->precheckCommand(self::CORE_COMMAND, $command)) {
                continue;
            }

            $config = $this->loadConfig(self::CORE_COMMAND, $command);

            if ($config === null) {
                continue;
            }

            /** @var Command $loadedCommand */
            $loadedCommand = new $command($config);
            if(!$loadedCommand->isConfigured()) {
                printf("Loading failed because class %s is not configured correctly\n", $command);
                continue;
            }

            if (method_exists($loadedCommand, 'install')) {
                $loadedCommand->install();
            }

            $this->setTrigger($loadedCommand, $config);

            printf("Completed\n");
        }
    }

    /**
     * Setup public commands to receive events
     *
     * @param array $commands
     */
    private function loadPublicCommands(array $commands): void
    {
        foreach ($commands as $command) {
            if (!$this->precheckCommand(self::PUBLIC_COMMAND, $command)) {
                continue;
            }

            $config = $this->loadConfig(self::PUBLIC_COMMAND, $command);

            if ($config === null) {
                continue;
            }

            /** @var Command $loadedCommand */
            $loadedCommand = new $command($config);

            if(!$loadedCommand->isConfigured()) {
                printf("Loading failed because class %s doesn't exist.\n", $command);
                continue;
            }

            if (method_exists($loadedCommand, 'install')) {
                $loadedCommand->install();
            }

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
    private function precheckCommand($namespace, $command): bool
    {
        $commandClass = substr($command, strlen($namespace));

        $type = ($namespace == self::CORE_COMMAND) ? 'core' : 'public';

        printf("%- 50s %s", "Loading {$type} command [{$commandClass}]", ":: ");

        if (!class_exists($command)) {
            printf("Loading failed because class %s doesn't exist.\n", $commandClass);
            return false;
        }

        if (!is_subclass_of($command, Command::class)) {
            printf("Loading failed because class %s doesn't extend %s.\n", $commandClass, Command::class);
            return false;
        }

        return true;
    }

    /**
     * @param Command $commandClass
     * @param array $config
     * @internal Add the a command command mapped to its corresponding command to the container
     *
     */
    private function setTrigger(Command $commandClass, array $config): void
    {
        if (array_key_exists('commands', $config['trigger'])) {
            $commands = $config['trigger']['commands'];
            foreach ($commands as $command) {
                $this->commands->push([$command => $commandClass]);
            }
        }
    }

    /**
     * @param $namespace
     * @param $command
     *
     * @return array|null
     * @internal Load a commands configuration
     *
     */
    private function loadConfig($namespace, $command): ?array
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
     * @return PromiseInterface
     */
    public function onMessage(Message $message): ?PromiseInterface
    {
        if ($message->author->bot ||
            $message->author->id === $message->client->user->id ||
            $message->guild === null) {
            return null;
        }

        if (Conversation::hasConversation($message->author)) {
            if(Str::lower($message->content) === Discord::$config['conversation']['safeword']) {
                Conversation::removeConversation($message->author);
                return null;
            }

            return call_user_func(Conversation::getConversation($message->author), $message)->then(function () use ($message){
                Conversation::removeConversation($message->author);
            })->otherwise(function ($reason) use ($message) {
               return $message->channel->send($reason);
            });
        }

        if(!Str::startsWith($message->content, Discord::$config['prefix'])) {
            return null;
        }

        $plainText = Str::lower(Str::after($message->content, Discord::$config['prefix']));
        $command = $this->findCommand($plainText);

        if ($command->isEmpty()) {
            return null;
        }

        $commandPattern = $command->keys()->first();
        $command = $command->first();

        return $command->execute($message, $plainText, $commandPattern);
    }

    /**
     * @param $text
     *
     * @return Collection
     * @internal Find a command for a chat command
     *
     */
    private function findCommand($text): Collection
    {
        return $this->commands->filter(function ($command) use ($text) {
            $key = \array_keys($command)[0];
            return $command[$key]->match($text, $key);
        })->collapse();
    }
}
