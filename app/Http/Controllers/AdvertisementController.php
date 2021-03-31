<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Advertisement;
use Chartisan\PHP\Chartisan;

class AdvertisementController extends Controller
{
    public function index(){

    }

    public function showAdvertisementList(){
        $data = Advertisement::with('getLastestPrice', 'getCategory', 'getType', 'getWebsite')->paginate(10);

        return view('advertisement.advertisementList')->with('data', $data);
    }

    public function showAdvertisement($id){
        $data = Advertisement::where('id', $id)->with('getDetails', 'getLastestPrice')->first();

        return view('advertisement.advertisement')->with('data', $data);
    }
}
