<?php

namespace App\Traits;

use App\Models\Advertisement;
use Carbon\Carbon;

trait ArchiveOldAdvertisemetsTrait {
    /**
     * Archives advertisements that where not updated for a certain amount of days
     *
     * @param integer $timeInDays How old the advertisement needs to be before it's archived. Default value 7 days
     * @return void
     */
    public function archiveAdvertisements($timeInDays = 7){
        $time = Carbon::now()->subDays($timeInDays)->toDateTimeString();

        $advertisements = Advertisement::where('updated_at', '<=', $time)->get();

        foreach($advertisements as $advert){
            $advert->archived = 1;
            $advert->save();
        }
    }
}