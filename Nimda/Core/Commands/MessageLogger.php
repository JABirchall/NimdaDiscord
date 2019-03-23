<?php

namespace Nimda\Core\Commands;

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