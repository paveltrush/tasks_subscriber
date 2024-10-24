<?php

namespace Broadcast;

class BroadcastHandler
{
    const TELEGRAM = 'telegram';
    const TEXT_LOCAL = 'text_local';
    private static $channels = [self::TELEGRAM, self::TEXT_LOCAL];

    /**
     * @throws \Exception
     */
    public static function createChannel(string $channel = self::TELEGRAM): BroadcastChannel
    {
        if ($channel == self::TELEGRAM){
            return new Telegram();
        }

        throw new \Exception("Channel '{$channel}' is not a valid channel");
    }
}