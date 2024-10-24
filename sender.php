<?php

require __DIR__.'/vendor/autoload.php';

use Telegram\Bot\Api;

$telegram = new Api('7306396024:AAHLbGW1tAXJvNR7NwpQGkAudBTKu6_inNk');


$response = $telegram->sendMessage([
    'chat_id' => '499769976',
    'text' => 'Hello World'
]);

$messageId = $response->getMessageId();

print_r($messageId);