<?php

namespace App\Traits;

use App\Models\Advertisement;
use Carbon\Carbon;

trait ArchiveOldAdvertisemetsTrait {
    public function archiveAdvertisements($timeInDays = 7){
        $time = Carbon::now()->subDays($timeInDays)->toDateTimeString();

        $advertisements = Advertisement::where('updated_at', '<=', $time)->get();

        foreach($advertisements as $advert){
            $advert->archived = 1;
            $advert->save();
        }
    }
}