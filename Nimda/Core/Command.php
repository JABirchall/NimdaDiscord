<?php

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\GuildMember;
use Illuminate\Support\Collection;
use Nimda\Configuration\Discord;

/**
 * Class Command
 * @package Nimda\Core
 */
abstract class Command
{
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
     */
    public function execute(Message $message, $plainText, $commandPattern)
    {
        if($this->middleware($message->member) === false) {
            return;
        }

        $arguments = $this->parseArguments($plainText, $commandPattern);
        if($arguments === false) {
            return;
        }

        if(Discord::$config['deleteCommands'] === true) {
            $message->delete(5);
        }

        $this->trigger($message, $arguments);
    }

    /**
     * Command trigger method triggered when a valid command has been matched.
     *
     * @param Message $message
     * @param array $args
     *
     * @return mixed
     */
    abstract public function trigger(Message $message, Collection $args = null);

    /**
     * Middleware is triggered before the command is ran to check authorization.
     * This method must be overridden by the commands specific middleware.
     *
     * @override
     * @param GuildMember $author
     *
     * @return bool|true
     */
    public function middleware(GuildMember $author): bool
    {
        return true;
    }

    /**
     * @internal Checks and parses a chat command arguments
     *
     * @param string $message
     * @param string $pattern
     *
     * @return Collection|false
     */
    private function parseArguments($message, $pattern)
    {
        $commandRegex = '/\{((?:(?!\d)\w)+?):?(?:(?<=\:)([[:graph:]]+))?\}/';
        $pattern = str_replace('/', '\/', $pattern);
        $names = [];

        $onMatch = function ($matches) use (&$names) {
            $pattern = $matches[2]??".*";
            $names[$matches[1]] = $matches[1];
            return "(?<{$matches[1]}>{$pattern})";
        };

        $regex = '/^'.\preg_replace_callback($commandRegex, $onMatch, $pattern).' ?/miu';
        $regexMatched = (bool)\preg_match($regex, $message, $matches);

        if($regexMatched === true)
        {
            return Collection::make($matches)->intersectByKeys($names);
        }

        return false;
    }
}