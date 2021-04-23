<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //


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
