<?php

namespace App\Http\Controllers;

use App\Mail\NotificationUpdateSend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendMail()
    {    
        //Mail::to('john.doe@gmail.com')->send(new NotificationUpdateSend());

        return redirect()->to('/');
    }
}