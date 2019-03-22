<?php

namespace Nimda\Core\Plugins;

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