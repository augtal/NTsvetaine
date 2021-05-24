<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationUpdateSend extends Mailable
{
    use Queueable, SerializesModels;

    private $message;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($message, $notification)
    {
        $this->message = $message;
        $this->notification = $notification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $link = "https:/ntsurinktuve.lt/notification/" . $this->notification->id;

        return $this
                ->subject('Gavote nauja pranešimą.')
                ->markdown('emails.notification.update')->with([
                    'message' => $this->message,
                    'link' => $link,
                    'notification' => $this->notification
                    ]);
    }
}
