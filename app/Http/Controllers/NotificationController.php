<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Notification;
use App\Models\Advertisement;
use App\Models\UserMessages;

class NotificationController extends Controller
{
    public function showNotificationsList(){
        $data = Notification::where('user_id', auth()->user()->id)->get();

        return view('notifications.notificationsList')->with('notifications', $data);
    }

    public function showNotification($id){
        $data = Notification::where('id', $id)->first();
        $mapData = Advertisement::with('getLocation')->get();

        $shapesData = json_decode($data->shapes, true);

        return view('notifications.notification')->with('mapData', $mapData)->with('data', $data)->with('shapesData', $shapesData);
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

    public function createNewMessageForUser($user_id, $notification_id){
        return;
    }

    public function calculateIfInside(Request $request){
        $data = $request->all();

        $vertices_x = array(37.628134, 37.629867, 37.62324, 37.622424);    
        $vertices_y = array(-77.458334,-77.449021,-77.445416,-77.457819); 
        $points_polygon = count($vertices_x) - 1;  
        $longitude_x = $_GET["longitude"];  
        $latitude_y = $_GET["latitude"];    

        if ($this->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
        echo "Is in polygon!";
        }
        else echo "Is not in polygon";
    }

    private function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
    {
    $i = $j = $c = 0;
    for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
        if ( (($vertices_y[$i]  >  $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
        ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) )
        $c = !$c;
    }
    return $c;
    }

    public function getUserMessages(){
        if(auth()->user() != null){
            $messages = UserMessages::where('user_id', auth()->user()->id)->get();
            return $messages;
        }

        return null;
    }
}
