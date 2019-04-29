<?php

namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\Message;
use Illuminate\Support\Collection;
use Nimda\Core\Command;
use Nimda\Core\Conversation;
use React\Promise\PromiseInterface;

class SayHello extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, Collection $args = null): PromiseInterface
    {
        Conversation::make($message, function (Message $message) {
            if($message->content === "repeat") {
                return Conversation::repeat("I will repeat for you, ". $this->config['message']);
            }

           return $message->reply("Nice you said: ". $message->content);
        });

        return $message->reply($this->config['message']);
    }
}