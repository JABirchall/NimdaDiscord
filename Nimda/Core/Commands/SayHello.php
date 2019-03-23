<?php

namespace Nimda\Core\Commands;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Core\Command;

class SayHello extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, array $args = [])
    {
        $message->reply($this->config['message']);
    }
}