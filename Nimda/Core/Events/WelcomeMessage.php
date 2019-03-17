<?php

namespace Nimda\Core\Events;

use CharlotteDunois\Yasmin\Models\GuildMember;
use Nimda\Core\UserEvent;
use \CharlotteDunois\Yasmin\Models\MessageEmbed;

class WelcomeMessage extends UserEvent
{
    public function userEventTrigger(GuildMember $member, GuildMember $memberOld = null)
    {
        if ($this->config['enabled'] === false || $this->config['channel'] === '') {
            return;
        }

        $channel = $member->guild->channels->get($this->config['channel']);

        if ($this->config['mention'] === true) {
            $channel->send("Welcome to {$member->guild->name}, {$member}");
        } elseif ($this->config['embed'] === true)
        {
            $embed = new MessageEmbed();
            $embed
                ->setTitle('Member Joined!')
                ->setColor(16777215)
                ->addField(" ID: ".$member, "Name: ".$member->displayName)
                ->setTimestamp()
                ->setFooter('User ID: '.$member->id);

            $channel->send('', array('embed' => $embed))
                ->done(null, function ($error) {
                    echo $error.PHP_EOL;
                });
        } else {
            echo "[WelcomeMessage] Mention and Embed are both set to false, no messages will display. If this is your intention, please set 'enable' to false!";
            return;
        }

    }
}