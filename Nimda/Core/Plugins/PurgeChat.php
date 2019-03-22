<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Configuration\Discord;
use Nimda\Core\Command;

class PurgeChat extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, array $args = [])
    {
        if($args['amount'] < 3) {
            $message->channel->send(sprintf("Invalid command parameters, usage: %s%s [amount min:3]", Discord::$config['prefix'], $this->config['trigger']['commands'][0] ))->then(function (Message $message) {
                $message->delete(10);
            });
            return;
        }

        $reason = sprintf("[PurgeChat] User %s issued purge command on #%s for %i messages", $message->author, $message->channel, $args['amount']);

        $message->channel->bulkDelete($args['amount'], $reason)->otherwise(function () use ($message) {
            $message->channel->send("Can not delete messages older then 14 days!")->then(function (Message $message) {
                $message->delete(10);
            });
        });
    }
}