<?php

declare(strict_types = 1);

namespace App\Charts;

use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\AdvertisementPrices;

class PriceChart extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {
        $id = $request->id;

        $data = AdvertisementPrices::where('advertisement_id', $id)->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc')->take(30)->get()->toArray();
        $data = array_reverse($data);

        $labels = [];
        $dataset = [];
        foreach($data as $info){
            $labels[] = substr($info['created_at'],0,-11);
            $dataset[] = $info['price'];
        }

        return Chartisan::build()
            ->labels($labels)
            ->dataset('Kaina', $dataset);
    }
}