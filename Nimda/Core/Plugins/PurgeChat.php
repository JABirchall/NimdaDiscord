<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Str;
use Nimda\Core\Plugin;

class PurgeChat extends Plugin
{
    public function trigger(Message $message)
    {
        $amount = (int)Str::after($message->content, $this->config['trigger']['commands'][0]);
        $amount = ($amount !== 0) ? $amount : $this->config['default'];

        $reason = sprintf("[PurgeChat] User %s issued purge command on #%s for %i messages", $message->author, $message->channel, $amount);
        $message->channel->bulkDelete($amount, $reason, Str::contains($message->content, "old"))->otherwise(function () use ($message) {
           $message->channel->send("Can not delete messages older then 14 days!");
        });
        //var_dump(Str::contains($message->content, "old"));
    }
}