<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Advertisement;
use App\Models\LikedAdvertisements;
use Chartisan\PHP\Chartisan;

use App\Models\AdvertCategories;
use App\Models\AdvertTypes;

class AdvertisementController extends Controller
{
    public function showAdvertisementList(Request $request){
        $filterInfo['types'] = AdvertTypes::get();
        $filterInfo['categories'] = AdvertCategories::get();

        $search = $request->input('search');
        $mapData = Advertisement::with('getLocation')->get();

        if($search != null){
            $search = explode(',', $search)[0];

            $data = Advertisement::query()
                    ->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('adress', 'LIKE', "%{$search}%")
                    ->paginate(10);
            $data->appends(['search' => $search]);
        }
        else{
            $data = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->paginate(10);
        }

        return view('listings.listingsList')->with('filterInfo', $filterInfo)->with('searchTerm', $search)->with('data', $data)->with('mapData', $mapData);
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
