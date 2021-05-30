<?php

namespace App\Traits;

use App\Models\UserMessages;
use App\Models\User;
use App\Models\Notification;
Use App\Models\NotificationAdvertisements;
use App\Models\AdvertisementPrices;

use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationUpdateSend;
use App\Traits\FindNotificationsTrait;

trait CreateUserMessageTrait {
    use FindNotificationsTrait;

    /**
     *  Checks if a notification message is needed to be sent
     *
     * @return void
     */
    public function sendNotifications(){
        $notifications = Notification::all();

        foreach($notifications as $notification){
            //1 Kai atsiranda naujas skelbimas zonoje
            if($notification->frequency == 1){
                $amount = $this->findAdsInsideNotification($notification);
                if($notification->advertisement_count < $amount){
                    $messageAddon = "Atsirano naujas skelbimas.";
                    $this->createNewMessage($notification, $messageAddon);

                    $notification->advertisement_count = $amount;
                    $notification->save();
                }
            }
            //2 Kada pasikeicia skelbimu zonoje kaina
            elseif($notification->frequency == 2){
                $advertisementList = NotificationAdvertisements::where('notification_id', $notification->id)->get();

                foreach($advertisementList as $singleAdvertisement){
                    $prices = AdvertisementPrices::where('advertisement_id', $singleAdvertisement->advertisement_id)->orderBy('updated_at', 'desc')->orderBy('created_at', 'desc')->take(2)->get()->toArray();

                    if($prices[0]['price'] != $prices[1]['price']){
                        $messageAddon = "Pasikeite skelbimo kaina.";
                        $this->createNewMessage($notification, $messageAddon);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Creates new notification message
     *
     * @param object $notification Notifcation object
     * @param string $messageAddon Message frenquency addon
     * @return void
     */
    private function createNewMessage($notification, $messageAddon){
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