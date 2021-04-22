<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Advertisement;
use App\Models\LikedAdvertisements;
use Chartisan\PHP\Chartisan;

class AdvertisementController extends Controller
{
    public function index(){

    }

    public function showAdvertisementList(){
        $data = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->paginate(10);

        $mapData = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->get();

        return view('listings.listingsList')->with('data', $data)->with('mapData', $mapData);
    }

    public function showAdvertisement($id){
        $data = Advertisement::where('id', $id)->with('getDetails', 'getLastestPrice')->first();
        
        $favourite = false;
        if(auth()->user())
            $favourite = LikedAdvertisements::where('user_id', auth()->user()->id)->where('advertisement_id', $id)->exists();

        return view('listings.listing')->with('data', $data)->with('favourite', $favourite);
    }

    public function favoritePage($id){
        $likedAd = LikedAdvertisements::where('user_id', auth()->user()->id)->where('advertisement_id', $id)->withTrashed()->first();

        #if user hasn't liked the advertisement before
        if($likedAd == null){
            $newLikedAd = new LikedAdvertisements();

            $newLikedAd->user_id = auth()->user()->id;
            $newLikedAd->advertisement_id = $id;
            $newLikedAd->save();
        }
        #if user wants to favorite advertisement again
        elseif($likedAd['deleted_at'] != null){
            $likedAd->restore();
        }
        #if user wants to remove advertisement from favorites
        else{
            $likedAd->delete();
        }

        return redirect()->back();
    }

    public function showNotificationConfirmPage(Request $request){
        $shapesData = json_decode($request->All()['saveShapesValues'], true);
        $mapData = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->get();

        return view('notifications.notificationConfirm')->with('mapData', $mapData)->with('shapesData', $shapesData);
    }

    public function saveNotification(){
        return redirect()->back();
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
}
