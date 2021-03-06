<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Login;

use App\Models\UserMessages;

class UserSessionListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $messages = UserMessages::where('user_id', auth()->user()->id)->orderBy('updated_at')->get()->toArray();
        $unreadMsgCount = UserMessages::where('user_id', auth()->user()->id)->where('read_msg', 0)->get()->count();

        session([
            'messages' => $messages,
            'unreadMsgCnt' => $unreadMsgCount
        ]);
    }
}
