<?php declare(strict_types=1);

namespace Nimda\Core;

use Carbon\Carbon;
use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\User;
use Illuminate\Support\Collection;
use Nimda\Configuration\Discord;
use React\Promise\ExtendedPromiseInterface;
use function React\Promise\reject;

class Conversation
{
    /**
     * @var Collection $conversations
     */
    private static $conversations;

    public static function init()
    {
        self::$conversations = new Collection;
    }

    /**
     * Starts a conversation with the user.
     *
     * @param Message $message
     * @param callable $next
     */
    public static function make(Message $message, callable $next)
    {
        $user = $message->author;
        if (self::hasConversation($user)) {
            self::removeConversation($user);
        }

        self::$conversations->push([
            'user' => $user->id,
            'callable' => $next,
            'timeout' => Carbon::now()->addMinutes(Discord::$config['conversation']['timeout'])->timestamp,
        ]);
    }

    /**
     * Reject the response from the user and repeat the conversation loop.
     *
     * @param string $reason
     * @return ExtendedPromiseInterface
     */
    public static function repeat(string $reason): ExtendedPromiseInterface
    {
        return reject($reason);
    }

    /**
     * Check if the current user already has an open conversation
     *
     * @param User $user
     * @return bool
     */
    public static function hasConversation(User $user): bool
    {
        return self::$conversations->where('user', $user->id)->isNotEmpty();
    }

    /**
     * Return the conversation callback for the current user.
     *
     * @param User $user
     * @return callable
     */
    public static function getConversation(User $user): callable
    {
        return self::$conversations->where('user', $user->id)->collapse()->get('callable');
    }

    /**
     * Delete the conversation of the current user effectively ending the conversation.
     *
     * @param User $user
     */
    public static function removeConversation(User $user)
    {
        self::$conversations = self::$conversations->reject(function ($value) use ($user) {
            return $value['user'] == $user->id;
        });
    }

    /**
     * Get all conversations
     *
     * @return Collection
     */
    public static function getConversations(): Collection
    {
        return self::$conversations;
    }

    /**
     * Refresh or rehash the conversations to remove stale conversations.
     */
    public static function refreshConversations()
    {
        self::$conversations = self::$conversations->reject(function ($value) {
            return $value['timeout'] <= Carbon::now()->timestamp;
        });
    }
}