<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\AdvertisementLocation;
use App\Models\NotificationAdvertisements;

trait FindNotificationsTrait {
    /**
     * Finds out if advertisement is inside notification shape if inside add it to that notification
     *
     * @param object $notification
     * @return integer $foundAmount Amount of advertisements found
     */
    public function findAdsInsideNotification($notification){
        $shapes = json_decode($notification->shapes, true);
        $foundAmount = 0;

        foreach($shapes as $shape){
            if($shape['type'] == 'polygon'){
                $shapeCordinates = $shape['cords'];
                array_push($shapeCordinates, $shapeCordinates[0]);

                $extra = 0.5;
                $maxLat = $this->calcAttributeArray($shapeCordinates, 'lat', 'max') + $extra;
                $minLat = $this->calcAttributeArray($shapeCordinates, 'lat', 'min') - $extra;
                $maxLng = $this->calcAttributeArray($shapeCordinates, 'lng', 'max') + $extra;
                $minLng = $this->calcAttributeArray($shapeCordinates, 'lng', 'min') - $extra;

                $points = AdvertisementLocation::whereBetween('lat', [$minLat, $maxLat])->
                                                whereBetween('lng', [$minLng, $maxLng])->get();

                foreach($points as $point){
                    if($this->pointInPolygon($point, $shapeCordinates)){
                        $notifiAdvert = NotificationAdvertisements::firstOrNew(
                            ['notification_id' => $notification->id, 
                            'advertisement_id' => $point->advertisement_id],);
                        
                        $notifiAdvert->save();
                        $foundAmount++;
                    }
                }
            }
            elseif($shape['type'] == 'circle'){
                $extra = 0.5;
                $maxLat = $shape['cords']['bounds']['north'] + $extra;
                $minLat = $shape['cords']['bounds']['south'] - $extra;
                $maxLng = $shape['cords']['bounds']['east'] + $extra;
                $minLng = $shape['cords']['bounds']['west'] - $extra;

                $points = AdvertisementLocation::whereBetween('lat', [$minLat, $maxLat])->
                                                whereBetween('lng', [$minLng, $maxLng])->get();

                foreach($points as $point){
                    if($this->pointInCircle($point, $shape['cords'])){
                        $notifiAdvert = NotificationAdvertisements::firstOrNew(
                            ['notification_id' => $notification->id, 
                            'advertisement_id' => $point->advertisement_id],);
                        
                        $notifiAdvert->save();
                        $foundAmount++;
                    }
                }
            }
        }

        return $foundAmount;
    }

    /**
     * Calculates max / min column value
     *
     * @param array $array Array in which to search
     * @param string $prop Column name by which to search
     * @param string $func Function by which to search (max/min)
     * @return integer $result Returns found value, if nothing is found returns false
     */
    private function calcAttributeArray($array, $prop, $func) {
        $result = array_column($array, $prop);
    
        if(function_exists($func)) {
            return $func($result);
        }
        return false;
    }

    /**
     * Checks if a point is inside a polygon
     *
     * @param array $point Point that is tested
     * @param array $vertices Poligons veritices
     * @return boolean
     */
    private function pointInPolygon($point, $vertices) {
        if ($this->pointOnVertex($point, $vertices)) return true;

        $intersections = 0; 
        for ($i=1; $i < count($vertices); $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];

            if ($vertex1['lng'] == $vertex2['lng'] && 
                $vertex1['lng'] == $point['lng'] && 
                //patikrinam ar tarp virsuniu horizontaliai lat asyje | lat=lat
                $point['lat'] > min($vertex1['lat'], $vertex2['lat']) && 
                $point['lat'] < max($vertex1['lat'], $vertex2['lat'])) 
                { 
                //yra ant linijos horizontalios
                return true;
            }

            if ($vertex1['lng'] != $vertex2['lng'] &&
                //patikrinam ar tarp virsuniu verticaliai lng asyje | lng=lng
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

    /**
     * Checks if point is a poligons vertex
     *
     * @param array $point Point that is tested
     * @param array $vertices Poligons veritices
     * @return boolean
     */
    private function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point['lat'] == $vertex['lat'] && $point['lng'] == $vertex['lng']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a point is inside a circle
     *
     * @param array $point Point that is tested
     * @param array $circle Circle information
     * @return void
     */
    private function pointInCircle($point, $circle){
        //pritaikysime Haversine formule
        $earth_radius = 6371;
    
        $dLat = deg2rad($circle['center']['lat'] - $point['lat']);  
        $dLon = deg2rad($circle['center']['lng'] - $point['lng']);  
    
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($point['lat'])) * cos(deg2rad($circle['center']['lat'])) * sin($dLon/2) * sin($dLon/2);  
        $c = 2 * asin(sqrt($a));  
        $dist = $earth_radius * $c;  

        //radius google api yra issaugomas metrais todel padaliname is 1000
        return $dist <= $circle['radius']/1000;
    }
}