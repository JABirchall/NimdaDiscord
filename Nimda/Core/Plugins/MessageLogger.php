<?php

namespace Nimda\Core\Plugins;

use CharlotteDunois\Yasmin\Models\Message;
use Nimda\Core\Command;

class MessageLogger extends Command
{
    /**
     * @inheritDoc
     */
    public function trigger(Message $message, array $args = [])
    {
        // TODO: Implement trigger() method.
    }
}