<?php declare(strict_types=1);

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\GuildMember;
use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Nimda\Configuration\Discord;
use React\Promise\PromiseInterface;

/**
 * Class Command
 * @package Nimda\Core
 */
abstract class Command
{
    const COMMAND_REGEX = '/\{((?:(?!\d)\w)+?):?(?:(?<=\:)([[:graph:]]+))?\}/';

    /**
     * @var array $config Configuration for the object
     */
    protected $config;

    /**
     * Command constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Perform actions to execute the command
     *
     * @param Message $message
     * @param $plainText
     * @param $commandPattern
     *
     * @return PromiseInterface
     */
    public function execute(Message $message, $plainText, $commandPattern): PromiseInterface
    {
        if ($this->middleware($message->member) === false) {
            return null;
        }

        $arguments = $this->parseArguments($plainText, $commandPattern);
        if ($arguments === false) {
            return null;
        }

        if (Discord::$config['deleteCommands'] === true) {
            $message->delete(5);
        }

        return $this->trigger($message, $arguments);
    }

    /**
     * Command trigger method triggered when a valid command has been matched.
     *
     * @param Message $message
     * @param Collection $args
     *
     * @return PromiseInterface
     */
    abstract public function trigger(Message $message, Collection $args = null): PromiseInterface;

    /**
     * Middleware is triggered before the command is ran to check authorization.
     * This method must be overridden by the commands specific middleware.
     *
     * @override
     * @param GuildMember $author
     *
     * @return bool
     */
    public function middleware(GuildMember $author): bool
    {
        return true;
    }

    /**
     * Check if the command is configured to be loaded.
     * Default check is for if a command is set. But should be overriden
     * with a commands specific requirements.
     *
     * @override
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->config['trigger']['commands'][0]);
    }

    /**
     * @param string $message
     * @param string $pattern
     *
     * @return Collection|false
     * @internal Checks and parses a chat command arguments
     *
     */
    private function parseArguments($message, $pattern): Collection
    {
        $names = [];
        $onMatch = function ($matches) use (&$names) {
            $pattern = $matches[2] ?? ".*";
            $names[$matches[1]] = $matches[1];
            return "?(?<{$matches[1]}>{$pattern})";
        };

        $pattern = \str_replace('/', '\/', $pattern);
        $regex = '/^' . \preg_replace_callback(self::COMMAND_REGEX, $onMatch, $pattern) . '/miu';
        $regexMatched = (bool)\preg_match($regex, $message, $matches);

        if ($regexMatched === true) {
            return Collection::make($matches)->intersectByKeys($names);
        }

        return false;
    }

    public function match($message, $pattern): bool
    {
        $onMatch = function ($matches) {
            $pattern = $matches[2] ?? ".*";
            return "?({$pattern})";
        };

        $pattern = \str_replace('/', '\/', $pattern);
        $regex = '/^' . \preg_replace_callback(self::COMMAND_REGEX, $onMatch, $pattern) . '/miu';
        return (bool)\preg_match($regex, $message, $matches);
    }
}