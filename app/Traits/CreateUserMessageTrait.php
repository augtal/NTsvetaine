<?php

namespace App\Traits;

use App\Models\UserMessages;

trait CreateUserMessageTrait {
    public function createNewMessage($notification, $messageAddon){
        $message = "Pranesime " . $notification->title . " atsirado pasikeitimas. " . $messageAddon;

        $userMessage = new UserMessages();
        $userMessage->user_id = $notification->user_id;
        $userMessage->notification_id = $notification->id;
        $userMessage->message = $message;
        $userMessage->read_msg = 0;
        $userMessage->save();

        return;
    }
}