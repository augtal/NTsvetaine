<?php

namespace App\Http\Controllers;

use App\Traits\FindNotificationsTrait;
use Illuminate\Http\Request;

use App\Models\Notification;
use App\Models\Advertisement;
use App\Models\UserMessages;

use App\Models\NotificationAdvertisements;

class NotificationController extends Controller
{
    use FindNotificationsTrait;

    public function showNotificationsList(){
        $data = Notification::where('user_id', auth()->user()->id)->get();

        return view('notifications.notificationsList')->with('notifications', $data);
    }

    public function showNotification($id){
        $notificationData = Notification::where('id', $id)->first();
        $mapData = Advertisement::with('getLocation')->get();

        $advertisementsIDs = NotificationAdvertisements::where('notification_id', $id)->pluck('advertisement_id')->toArray();
        $advertisements = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->whereIn('id', $advertisementsIDs)->orderBy('updated_at','desc')->paginate(10);

        $shapesData = json_decode($notificationData->shapes, true);

        return view('notifications.notification')->with('advertisements', $advertisements)->with('mapData', $mapData)->with('notificationData', $notificationData)->with('shapesData', $shapesData);
    }

    public function showNotificationConfirmPage(Request $request){
        $shapesData = json_decode($request->All()['saveShapesValues'], true);
        $mapData = Advertisement::with('getLocation')->get();

        return view('notifications.notificationConfirm')->with('mapData', $mapData)->with('shapesData', $shapesData);
    }

    public function saveNotification(Request $request){
        $data = $request->All();

        $notification = new Notification();
        $notification->user_id = auth()->user()->id;
        $notification->title = $data['title'];
        $notification->description = $data['description'];
        $notification->frequency = (int)$data['frequency'];
        $notification->shapes = $data['confirmShapesValues'];
        $notification->advertisement_count = 0;
        $notification->save();

        $amount = $this->findAdsInsideNotification($notification->id);

        $notification->advertisement_count = $amount;
        $notification->save();
        
        return redirect('notifications');
    }

    public function showEditNotificationPage($id){
        $data = Notification::where('id', $id)->first();
        $mapData = Advertisement::with('getLocation')->get();

        $shapesData = json_decode($data->shapes, true);

        return view('notifications.notificationEdit')->with('mapData', $mapData)->with('shapesData', $shapesData)->with('data', $data);
    }

    public function editNotification(Request $request, $id){
        $data = $request->All();
    
        $notification = Notification::where('id', $id)->first();
        $notification->user_id = auth()->user()->id;
        $notification->title = $data['title'];
        $notification->description = $data['description'];
        $notification->frequency = (int)$data['frequency'];
        $notification->shapes = $data['confirmShapesValues'];
        $notification->save();
        
        return redirect('notifications');
    }

    public function deleteNotification($id){
        $notification = Notification::where('id', $id)->first();
        $notification->delete();
        return redirect()->back();
    }

    public function getUserMessages(){
        if(auth()->user() != null){
            $messages = UserMessages::where('user_id', auth()->user()->id)->get();
            return $messages;
        }

        return null;
    }

    public function createNewMessageForUser($user_id, $notification_id){
        return;
    }
}
