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
}
