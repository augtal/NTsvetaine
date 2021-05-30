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

    /**
     * Shows all notifications
     *
     * @return view notifications.notificationsList
     */
    public function showNotificationsList(){
        $data = Notification::where('user_id', auth()->user()->id)->get();

        return view('notifications.notificationsList')->with('notifications', $data);
    }

    /**
     * Shows single notification
     *
     * @param integer $id Notification ID
     * @return view notifications.notification
     */
    public function showNotification($id){
        $notificationData = Notification::where('id', $id)->first();
        $mapData = Advertisement::with('getLocation')->get();

        $advertisementsIDs = NotificationAdvertisements::where('notification_id', $id)->pluck('advertisement_id')->toArray();
        $advertisements = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->whereIn('id', $advertisementsIDs)->orderBy('updated_at','desc')->paginate(10);

        $shapesData = json_decode($notificationData->shapes, true);

        return view('notifications.notification')->with('advertisements', $advertisements)->with('mapData', $mapData)->with('notificationData', $notificationData)->with('shapesData', $shapesData);
    }

    /**
     * Shows notification final confimation page
     *
     * @param Request $request
     * @return view notifications.notificationConfirm
     */
    public function showNotificationConfirmPage(Request $request){
        if($request->input('saveShapesValues')){
            $shapesData = json_decode($request->input('saveShapesValues'), true);
        }
        else{
            $shapesData = array();
        }
        $mapData = Advertisement::with('getLocation')->get();

        return view('notifications.notificationConfirm')->with('mapData', $mapData)->with('shapesData', $shapesData);
    }

    /**
     * Saves notification to database
     *
     * @param Request $request
     * @return redirect notifications
     */
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

        $amount = $this->findAdsInsideNotification($notification);

        $notification->advertisement_count = $amount;
        $notification->save();
        
        return redirect('notifications');
    }

    /**
     * Shows notification edit page
     *
     * @param integer $id Notification ID
     * @return view notifications.notificationEdit
     */
    public function showEditNotificationPage($id){
        $data = Notification::where('id', $id)->first();
        $mapData = Advertisement::with('getLocation')->get();

        $shapesData = json_decode($data->shapes, true);

        return view('notifications.notificationEdit')->with('mapData', $mapData)->with('shapesData', $shapesData)->with('data', $data);
    }

    /**
     * Save notifcation changes to database
     *
     * @param Request $request
     * @param integer $id Notification ID
     * @return redirect notifications
     */
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

    /**
     * Daletes notification from database
     *
     * @param integer $id Notification ID
     * @return void
     */
    public function deleteNotification($id){
        $notification = Notification::where('id', $id)->first();

        NotificationAdvertisements::where('notification_id', $notification->id)->delete();

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
}
