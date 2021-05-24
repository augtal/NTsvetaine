<?php

namespace App\Traits;

use App\Models\UserMessages;
use App\Models\User;

use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationUpdateSend;

trait CreateUserMessageTrait {
    public function createNewMessage($notification, $messageAddon){
        $message = "Pranesime " . $notification->title . " atsirado pasikeitimas. " . $messageAddon;

        $userMessage = new UserMessages();
        $userMessage->user_id = $notification->user_id;
        $userMessage->notification_id = $notification->id;
        $userMessage->message = $message;
        $userMessage->read_msg = 0;
        $userMessage->save();

        $user = User::find($notification->user_id);

        Mail::to($user->email)->send(new NotificationUpdateSend($message, $notification));

        return;
    }
}