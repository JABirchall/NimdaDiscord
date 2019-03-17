<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Core\Plugin;

class SayHello extends Plugin
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, array $args = [])
    {
        $message->reply($this->config['message']);
    }
}