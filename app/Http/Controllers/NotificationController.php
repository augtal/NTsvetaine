<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Notification;
use App\Models\Advertisement;
use App\Models\AdvertisementLocation;
use App\Models\UserMessages;

use App\Models\NotificationAdvertisements;

class NotificationController extends Controller
{
    public function showNotificationsList(){
        $data = Notification::where('user_id', auth()->user()->id)->get();

        return view('notifications.notificationsList')->with('notifications', $data);
    }

    public function showNotification($id){
        $notificationData = Notification::where('id', $id)->first();
        $mapData = Advertisement::with('getLocation')->get();

        $advertisementsIDs = NotificationAdvertisements::where('notification_id', $id)->pluck('advertisement_id')->toArray();
        $advertisements = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->whereIn('id', $advertisementsIDs)->paginate(10);

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
        $notification->save();

        $this->findAdsInsideNotification($notification->id);
        
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

    public function findAdsInsideNotification($notificationID=3){
        $notification = Notification::find($notificationID);

        $shapes = json_decode($notification->shapes, true);

        foreach($shapes as $shape){
            if($shape['type'] == 'polygon'){
                $shapeCordinates = $shape['cords'];
                array_push($shapeCordinates, $shapeCordinates[0]);

                $extra = 0.5;
                $maxLat = $this->calc_attribute_in_array($shapeCordinates, 'lat', 'max') + $extra;
                $minLat = $this->calc_attribute_in_array($shapeCordinates, 'lat', 'min') - $extra;
                $maxLng = $this->calc_attribute_in_array($shapeCordinates, 'lng', 'max') + $extra;
                $minLng = $this->calc_attribute_in_array($shapeCordinates, 'lng', 'min') - $extra;

                $points = AdvertisementLocation::whereBetween('lat', [$minLat, $maxLat])->
                                                whereBetween('lng', [$minLng, $maxLng])->get();

                foreach($points as $point){
                    if($this->pointInPolygon($point, $shapeCordinates)){
                        $inside = $point->advertisement_id;
                        echo $inside . "<br>";

                        $notifiAdvert = NotificationAdvertisements::firstOrNew(
                            ['notification_id' => $notificationID, 
                            'advertisement_id' => $point->advertisement_id],);
                        
                        $notifiAdvert->save();
                    }
                }
            }
            elseif($shape['type'] == 'circle'){

            }
            elseif($shape['type'] == 'rectangle'){
                
            }
        }
    }

    function calc_attribute_in_array($array, $prop, $func) {
        $result = array_column($array, $prop);
    
        if(function_exists($func)) {
            return $func($result);
        }
        return false;
    }

    private function pointInPolygon($point, $vertices) {
        if ($this->pointOnVertex($point, $vertices)) return true;

        $intersections = 0; 
        for ($i=1; $i < count($vertices); $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];

            if ($vertex1['lng'] == $vertex2['lng'] && 
                $vertex1['lng'] == $point['lng'] && 
                //patikrinam ar tarp virsuniu horizontaliai x asyje | x=lat
                $point['lat'] > min($vertex1['lat'], $vertex2['lat']) && 
                $point['lat'] < max($vertex1['lat'], $vertex2['lat'])) 
                { 
                //yra ant linijos horizontalios
                return true;
            }

            if ($vertex1['lng'] != $vertex2['lng'] &&
                //patikrinam ar tarp virsuniu verticaliai y asyje | y=lng
                $point['lng'] > min($vertex1['lng'], $vertex2['lng']) &&
                $point['lng'] <= max($vertex1['lng'], $vertex2['lng']) &&
                $point['lat'] < max($vertex1['lat'], $vertex2['lat'])) 
                { 
                
                $toWall = ($point['lng'] - $vertex1['lng']) * ($vertex2['lat'] - $vertex1['lat']) / ($vertex2['lng'] - $vertex1['lng']) + $vertex1['lat']; 
                
                if ($toWall == $point['lat']) {
                    //yra ant linijos verticalios
                    return true;
                }
                
                if ($vertex1['lat'] == $vertex2['lat'] || $point['lat'] <= $toWall) {
                    $intersections++; 
                }
            } 
        } 
        
        if ($intersections % 2 != 0) {
            return true;
        } else {
            return false;
        }
    }

    private function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point['lat'] == $vertex['lat'] && $point['lng'] == $vertex['lng']) {
                return true;
            }
        }
    }
}
