<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Advertisement;
use App\Models\LikedAdvertisements;

use App\Models\AdvertCategories;
use App\Models\AdvertTypes;
use App\Models\REWebsites;

class AdvertisementController extends Controller
{
    /**
     * Shows all advertisements
     *
     * @param Request $request
     * @return view listings.listingsList
     */
    public function showAdvertisementList(Request $request){
        $filterInfo['types'] = AdvertTypes::get();
        $filterInfo['categories'] = AdvertCategories::get();
        $filterInfo['REwebsites'] = REWebsites::get();

        $search = $request->input('search');
        $filterArray = $request->input('filters');
        $mapData = Advertisement::with('getLocation')->get();

        $dataQuery = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->orderBy('updated_at', 'DESC');

        if($search != null){
            session()->put('search', $search);
            $searchParam = explode(',', $search)[0];
            $searchTerm = substr($searchParam, 0, strlen($searchParam)-2);

            $dataQuery = Advertisement::query()
                    ->where('title', 'LIKE', "%{$searchTerm}%")
                    ->Where('adress', 'LIKE', "%{$searchTerm}%");
        }

        if($filterArray){
            $dataQuery = $this->filter($filterArray, $dataQuery);
        }

        $data = $dataQuery->orderBy('updated_at', 'DESC')->orderBy('created_at', 'DESC')->paginate(6)->withQueryString();

        if(strlen(session()->get('search')) > 0) 
            $data->appends(['search' => $search]);
        
        return view('listings.listingsList')->with('filterInfo', $filterInfo)->with('filterData', $filterArray)->with('searchTerm', $search)->with('data', $data)->with('mapData', $mapData);
    }

    /**
     * Shows single advertisement
     *
     * @param integer $id Advertisement ID
     * @return view listings.listing
     */
    public function showAdvertisement($id){
        $data = Advertisement::where('id', $id)->with('getDetails', 'getLastestPrice')->first();
        
        $favourite = false;
        if(auth()->user())
            $favourite = LikedAdvertisements::where('user_id', auth()->user()->id)->where('advertisement_id', $id)->exists();

        return view('listings.listing')->with('data', $data)->with('favourite', $favourite);
    }

    /**
     * Makes advertisement favorite
     *
     * @param integer $id Advertisement ID
     * @return void
     */
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

    /**
     * Administrator archives selected advertisement
     *
     * @param integer $id Advertisement ID
     * @return void
     */
    public function archiveAdvertisement($id){
        $advertisement = Advertisement::find($id);

        if($advertisement->archived == 1){
            $advertisement->archived = 0;
            $advertisement->save();
        }
        else{
            $advertisement->archived = 1;
            $advertisement->save();
        }

        return redirect()->back();
    }

    /**
     * Adds filtering to eloquent query
     *
     * @param array $filterArray Array that has filter information
     * @param builder $dataQuery Eloquent builder
     * @return builder $dataQuery 
     */
    private function filter($filterArray, $dataQuery){
        if(array_key_exists('min_price', $filterArray) && $filterArray['min_price'] != null){
            $dataQuery->whereHas('getLastestPrice', function ($query) use (&$filterArray) {
                $query->where('price', '>', $filterArray['min_price']);
            });
        }
        if(array_key_exists('max_price', $filterArray) && $filterArray['max_price'] != null){
            $dataQuery->whereHas('getLastestPrice', function ($query) use (&$filterArray) {
                $query->where('price', '<', $filterArray['max_price']);
            });
        }
        if(array_key_exists('type', $filterArray) && $filterArray['type'] != null){
            $dataQuery->whereHas('getType', function ($query) use (&$filterArray) {
                $query->where('id', $filterArray['type']);
            });
        }
        if(array_key_exists('category', $filterArray) && $filterArray['category'] != null){
            $dataQuery->whereHas('getCategory', function ($query) use (&$filterArray) {
                $query->where('id', $filterArray['category']);
            });
        }
        if(array_key_exists('REwebsites', $filterArray) && $filterArray['REwebsites'] != null){
            $dataQuery->whereHas('getWebsite', function ($query) use (&$filterArray) {
                $query->where('id', $filterArray['REwebsites']);
            });
        }
        if(array_key_exists('archived', $filterArray)){
            $dataQuery->where('archived', 0);
        }

        return $dataQuery;
    }
}
