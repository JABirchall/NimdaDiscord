<?php declare(strict_types=1);

namespace Nimda\Core;

use CharlotteDunois\Yasmin\Models\Message;
use CharlotteDunois\Yasmin\Models\User;
use Illuminate\Support\Collection;

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

    public static function make(Message $message, callable $next)
    {
        $user = $message->author;
        if (self::hasConversation($user)) {
            self::removeConversation($user);
        }

        self::$conversations->push([
            "user" => $user->id,
            "callable" => $next
        ]);
    }

    public static function hasConversation(User $user): bool
    {
        return self::$conversations->where('user', $user->id)->isNotEmpty();
    }

    public static function getConversation(User $user): callable
    {
        return self::$conversations->where('user', $user->id)->toArray()[0]['callable'];
    }

    public static function removeConversation(User $user)
    {
        self::$conversations = self::$conversations->reject(function ($value, $key) use ($user) {
            $reject = $value['user'] == $user->id;
            return $reject;
        });
    }

    public static function getConversations(): Collection
    {
        return self::$conversations;
    }


}