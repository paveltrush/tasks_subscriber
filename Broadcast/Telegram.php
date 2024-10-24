<?php

namespace Broadcast;

use Message\TaskCollection;
use Telegram\Bot\Api;

class Telegram implements BroadcastChannel
{
    const API = '7306396024:AAHLbGW1tAXJvNR7NwpQGkAudBTKu6_inNk';
    const CHAT_ID = '499769976';
    private Api $telegram;

    public function __construct()
    {
        $this->telegram = new Api(self::API);
    }

    public function sendTasksNotification(TaskCollection $tasks)
    {
        return $this->telegram->sendMessage([
            'chat_id' => self::CHAT_ID,
            'text' => $tasks->toMonospacedText(),
            'parse_mode' => 'HTML'
        ]);
    }
}