<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Advertisement;

class AdvertisementController extends Controller
{
    public function index(){

    }

    public function showAdvertisementList(){
        $data = Advertisement::with('lastestPrice')->simplePaginate(15);
        $data2 = Advertisement::with('lastestPrice')->get()->toArray();

        return view('advertisementList')->with('data', $data)->with('data2', $data2);
    }
}
