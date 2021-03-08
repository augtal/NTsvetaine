<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;

class AdvertisementController extends Controller
{
    public function index(){

    }

    public function showAdvertisementList(){
        $data = Advertisement::with('lastestPrice', 'getCategory', 'getType', 'getWebsite')->paginate(10);

        return view('advertisementList')->with('data', $data);
    }
}
