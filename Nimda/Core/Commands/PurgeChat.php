<?php

namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\GuildMember;
use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Nimda\Configuration\Discord;
use Nimda\Core\Command;

class PurgeChat extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, Collection $args = null)
    {
        if ($args->get('amount') < 3) {
            $message->channel->send(sprintf("Invalid command parameters, usage: %s%s [amount min:3]",
                Discord::$config['prefix'], $this->config['trigger']['commands'][0]))->then(function (Message $message
            ) {
                $message->delete(10);
            });
            return;
        }

        $reason = sprintf("[PurgeChat] User %s issued purge command on #%s for %i messages", $message->author,
            $message->channel, $args->get('amount'));

        $message->channel->bulkDelete($args->get('amount'), $reason)->otherwise(function () use ($message) {
            $message->channel->send("Can not delete messages older then 14 days!")->then(function (Message $message) {
                $message->delete(10);
            });
        });
    }

    public function middleware(GuildMember $author): bool
    {
        return Collection::make($this->config['roles'])
            ->intersect($author->roles->keys()->all())
            ->isNotEmpty();
    }
}