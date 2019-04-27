<?php

namespace Nimda\Core\Events;

use CharlotteDunois\Yasmin\Interfaces\TextChannelInterface;
use CharlotteDunois\Yasmin\Models\GuildMember;
use CharlotteDunois\Yasmin\Models\MessageEmbed;
use Nimda\Core\Event;

class WelcomeMessage extends Event
{
    /**
     * @inheritDoc
     */
    public function guildMemberAdd(GuildMember $member)
    {
        /* @var TextChannelInterface $channel */
        $channel = $member->guild->channels->get($this->config['channel']);

        if ($this->config['mention'] === true) {
            return $channel->send("Welcome to {$member->guild->name}, {$member}");
        } elseif ($this->config['embed'] === true) {
            $embed = new MessageEmbed();
            $embed->setTitle('Member Joined!')
                ->addField(" ID: " . $member, "Name: " . $member->displayName)
                ->setColor(16777215)
                ->setTimestamp()
                ->setFooter('User ID: ' . $member->id);

            return $channel->send('', ['embed' => $embed])
                ->otherwise(function ($error) {
                    echo $error . PHP_EOL;
                });
        }
    }
}