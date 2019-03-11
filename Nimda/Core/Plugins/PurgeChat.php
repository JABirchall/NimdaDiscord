<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Configuration\Discord;
use Nimda\Core\Plugin;

class PurgeChat extends Plugin
{
    public function trigger(Message $message, $text = null)
    {
        [$amount, $old] = explode(' ', $text);

        if($amount < 3) {
            $message->channel->send(sprintf("Invalid commands parameters, usage: %s%s [amount min:3]", Discord::$config['prefix'], $this->config['trigger']['commands'][0] ))->then(function (Message $message) {
                $message->delete(5);
            });
            return;
        }

        $reason = sprintf("[PurgeChat] User %s issued purge command on #%s for %i messages", $message->author, $message->channel, $amount);

        $message->channel->bulkDelete($amount, $reason, $old ? true : false)->otherwise(function () use ($message) {
            $message->channel->send("Can not delete messages older then 14 days!")->then(function (Message $message) {
                $message->delete(5);
            });
        });
    }
}